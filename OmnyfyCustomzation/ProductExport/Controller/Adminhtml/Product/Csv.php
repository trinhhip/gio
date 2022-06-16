<?php


namespace OmnyfyCustomzation\ProductExport\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Omnyfy\Vendor\Model\Resource\Vendor;
use Omnyfy\Vendor\Model\VendorFactory;

class Csv extends \Magento\Backend\App\Action
{
    /**
     * @var WriteInterface
     */
    protected $directory;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;
    /**
     * @var Vendor
     */
    protected $vendor;
    /**
     * @var VendorFactory
     */
    protected $vendorFactory;
    /**
     * @var FileFactory
     */
    protected $fileFactory;


    public function __construct(
        Context $context,
        Filesystem $filesystem,
        CollectionFactory $collectionFactory,
        StockRegistryInterface $stockRegistry,
        Vendor $vendor,
        VendorFactory $vendorFactory,
        FileFactory $fileFactory
    )
    {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->collectionFactory = $collectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->vendor = $vendor;
        $this->vendorFactory = $vendorFactory;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    public function getHeader()
    {
        return [
            __('Name'),
            __('Thumbnail'),
            __('Brand'),
            __('SKU'),
            __('Price'),
            __('Categories'),
            __('Length'),
            __('Width'),
            __('Height'),
            __('Weight'),
            __('Dimensions'),
            __('Place of Origin'),
            __('MOQ'),
            __('HS Code'),
            __('Primary Material'),
            __('MOQ lead time'),
            __('Large order lead time'),
            __('Ship From Country'),
            __('Calculated Shipping Weight')
        ];
    }

    public function execute()
    {
        $file = 'export/product_listing_' . md5(microtime()) . '.csv';
        $selected = $this->getRequest()->getParam('selected');
        $collection = $this->collectionFactory->create()->addAttributeToSelect('*');
        $collection->addFieldToSelect('vendor_name');
        if ($selected !== 'false') {
            $collection->addIdFilter($selected);
        }
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->getHeader());
        foreach ($collection->getItems() as $item) {
            $rowData = $this->getRowData($item);
            $stream->writeCsv($rowData);
        }
        $stream->unlock();
        $stream->close();
        $csvFiles = [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
        $fileName = 'product_export_' . date('ymd') . '.csv';
        return $this->fileFactory->create($fileName, $csvFiles, DirectoryList::VAR_DIR);

    }

    public function getRowData($product)
    {
        return [
            'name' => $product->getName(),
            'thumbnail' => $product->getThumbnail(),
            'brand' => $this->getVendorByProductId($product->getId())->getName(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
            'categories' => $this->getCategories($product->getCategoryCollection()),
            'omnyfy_dimensions_length' => $product->getOmnyfyDimensionsLength(),
            'omnyfy_dimensions_width' => $product->getOmnyfyDimensionsWidth(),
            'omnyfy_dimensions_height' => $product->getOmnyfyDimensionsHeight(),
            'weight' => $product->getWeight(),
            'sw_dimensions' => $product->getSwDimensions(),
            'sw_place_of_origin' => $this->getAttributeValue($product, 'sw_place_of_origin'),
            'moq' => (int)$this->getMOQ($product),
            'hs_code' => $product->getHsCode(),
            'primary_material' => $this->getAttributeValue($product, 'primary_material'),
            'lead_time' => $this->getAttributeValue($product, 'lead_time'),
            'large_order_lead_time' => $product->getLargeOrderLeadTime(),
            'ship_from_country' => $this->getAttributeValue($product, 'ship_from_country'),
            'calculated_shipping_weight' => $product->getCalculatedShippingWeight()
        ];
    }


    protected function getCategories($categories)
    {
        $categories->addAttributeToSelect('*');
        $categoryName = [];
        foreach ($categories as $category) {
            $categoryName[] = $category->getName();
        }
        return implode(', ', $categoryName);
    }

    protected function getAttributeValue($product, $attributeCode)
    {
        return $product->getResource()->getAttribute($attributeCode)->getFrontend()->getValue($product);
    }

    protected function getMOQ($product)
    {
        $stock = $this->stockRegistry->getStockItem($product->getId());
        return $stock->getMinSaleQty();
    }

    protected function getVendorByProductId($productId)
    {
        $vendorId = $this->vendor->getVendorIdByProductId($productId);
        return $this->vendorFactory->create()->load($vendorId);
    }
}