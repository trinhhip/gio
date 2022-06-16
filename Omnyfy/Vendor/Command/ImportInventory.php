<?php
/**
 * Project: Omnyfy Multi Vendor.
 * User: jing
 * Date: 18/4/17
 * Time: 2:10 PM
 */

namespace Omnyfy\Vendor\Command;

use Omnyfy\Core\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ImportInventory extends Command
{
    protected $csvProcessor;

    protected $inventoryResource;

    protected $productCollectionFactory;

    protected $locationCollectionFactory;

    protected $vendorCollectionFactory;

    protected $vendorResource;

    protected $appState;

    protected $config;

    protected $vSourceStockResource;

    protected $vSourcecStockCollectionFactory;

    protected $resourceConnection;

    protected $sourceModelFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\Location\CollectionFactory $locationCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory $vendorCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource,
        \Omnyfy\Vendor\Model\Resource\Inventory $inventoryResource,
        \Magento\Framework\File\Csv $csvProcessor,
        \Omnyfy\Vendor\Model\Config $config,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock\CollectionFactory $vSourcecStockCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Inventory\Model\SourceFactory $sourceModelFactory
    )
    {
        $this->appState = $state;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->vendorResource = $vendorResource;
        $this->inventoryResource = $inventoryResource;
        $this->csvProcessor = $csvProcessor;
        $this->config = $config;
        $this->vSourceStockResource = $vSourceStockResource;
        $this->vSourcecStockCollectionFactory = $vSourcecStockCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->sourceModelFactory = $sourceModelFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('omnyfy:vendor:import_inventory');
        $this->setDescription('Import product source relation');
        $this->addArgument('filename', InputArgument::REQUIRED, 'Filename (abcd.csv)');
        $this->addArgument('qty', InputArgument::OPTIONAL, 'Default Qty');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('Someone else is running this command.');
            return;
        }

        try{
            $code = $this->appState->getAreaCode();
        }
        catch(\Exception $e) {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        }

        $output->writeln('Import Source product relation');

        //load all location
        // $locations = [];
        // $locationCollection = $this->locationCollectionFactory->create();
        // foreach($locationCollection as $location) {
        //     $locations[$location->getId()] = $location->getVendorId();
        // }

        //load all source stock
        $sourceStocks = [];
        $vSourceStockCollection = $this->vSourcecStockCollectionFactory->create();
        foreach($vSourceStockCollection as $vSourceStock) {
            $sourceStocks[$vSourceStock->getId()] = $vSourceStock->getVendorId();
        }

        //load all vendor
        $vendorCollection = $this->vendorCollectionFactory->create();
        $vendors = $vendorCollection->getAllIds();

        $filename = $input->getArgument('filename');
        $output->writeln('Loading file '.$filename);
        $defaultQty = $input->getArgument('qty');
        $defaultQty = empty($defaultQty) ? 0 : intval($defaultQty);
        $csvRows = $this->csvProcessor->getData($filename);

        $productIdsToSource = [];
        $productIdsToVendor = [];
        $skus = [];
        foreach($csvRows as $key => $row) {
            if (0== $key) continue;

            if (empty($row[0])) {
                $output->writeln('SKU missing on line '. ($key +1));
                continue;
            }

            if (empty($row[1])) {
                $output->writeln('Source Stock Id missing on line '.($key + 1));
                continue;
            }
            $sku = $row[0];
            $sourceStockId = $row[1];
            $sourceStockIdsBySourceCode = $this->vSourceStockResource->getSourceStockIdWithSameSourceCode($sourceStockId);
            $qty = isset($row[3]) ? intval($row[3]) : $defaultQty;

            if (!array_key_exists($sourceStockId, $sourceStocks)) {
                $output->writeln('Source Stock ID '. $sourceStockId . ' not exist on line '.($key + 1));
                continue;
            }
            $vendorId = $sourceStocks[$sourceStockId];
            if (!in_array($vendorId, $vendors)) {
                $output->writeln('Invalid Vendor ID '. $vendorId . ' for source stock '.$sourceStockId . ' on line '.($key + 1));
                continue;
            }
            if (count($sourceStockIdsBySourceCode) == 1) {
                $skus[$sku] = [
                    'source_stock_id' => $sourceStockId,
                    'vendor_id' => $vendorId,
                    'qty' => $qty,
                    'line' => $key + 1
                ];
            } else {
                $skus[$sku][] = [
                    'source_stock_id' => $sourceStockIdsBySourceCode,
                    'vendor_id' => $vendorId,
                    'qty' => $qty,
                    'line' => $key + 1
                ];
            }
        }

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addFieldToFilter('sku', ['in' => array_keys($skus)]);

        $skuToIds = [];
        foreach($productCollection->getData() as $product) {
            $skuToIds[$product['sku']] = $product['entity_id'];
        }
        $productCollection->clear();
        reset($sourceStocks);
        reset($vendors);

        $sourceModel = $this->sourceModelFactory->create();

        $zendDbExprNull = new \Zend_Db_Expr('NULL');
        foreach($skus as $sku => $arr) {
            if (!array_key_exists($sku, $skuToIds)) {
                $output->writeln('Product '.$sku.' in line '. $arr['line'] . ' not exist');
                continue;
            }

            $sourceStockId = $arr['source_stock_id'];
            $sourceCode = $this->vSourceStockResource->getSourceCodeById($sourceStockId);
            $vendorId = $arr['vendor_id'];
            $productId = $skuToIds[$sku];
            $vendorIdOfProduct = $this->getVendorIdOfProduct($productId);

            if ($vendorIdOfProduct != $vendorId) {
                $output->writeln('Product ' . $sku . ' and Source Stock ID ' . $sourceStockId . 'in line ' . $arr['line'] . ' not the same Vendor');
                continue;
            }
            if ($this->config->isVendorShareProducts()) {
                $productIdsToVendor[$productId] = $vendorId;
            }
            else {
                if (!array_key_exists($skuToIds[$sku], $productIdsToVendor) ) {
                    $productIdsToVendor[$productId] = $vendorId;
                }
                else{
                    $output->writeln('Product '.$sku. ' in line '. $arr['line'] . ' already assigned to Vendor '. $productIdsToVendor[$productId]);
                    continue;
                }
            }

            $sourceModel->load($sourceCode);

            $productIdsToSource = [
                'sku' => $sku,
                'inventory_id' => $zendDbExprNull,
                'product_id' => $productId,
                'source_code' => $sourceCode,
                'quantity' => $arr['qty'],
                'source_stock_id' => $arr['source_stock_id']
            ];

            $assignedSource[0] = [
                'source_code' => $sourceCode,
                'name' => $sourceModel->getName(),
                'quantity' => $arr['qty'],
                'source_status' => '1',
                'notify_stock_qty' => '1',
                'notify_stock_qty_use_default' => '1',
                'position' => '1',
                'status' => '1'
            ];

            $this->inventoryResource->importSave($productIdsToSource, $assignedSource);
        }

        $productIdToVendorId = [];
        foreach($productIdsToVendor as $productId => $vendorId) {
            $productIdToVendorId[] = [
                'product_id' => $productId,
                'vendor_id' => $vendorId
            ];
        }
        $this->vendorResource->saveProductRelation($productIdToVendorId);

        $output->writeln('Done');
    }

    public function getVendorIdOfProduct($productId) {
        $conn = $this->resourceConnection->getConnection();
        $query = $conn->select()->from('omnyfy_vendor_vendor_product', 'vendor_id')->where('product_id = ?', $productId);
        $result = $conn->fetchOne($query);
        return $result;
    }
}
