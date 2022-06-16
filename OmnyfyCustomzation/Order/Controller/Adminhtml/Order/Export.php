<?php


namespace OmnyfyCustomzation\Order\Controller\Adminhtml\Order;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Omnyfy\Vendor\Model\Resource\Vendor;
use Omnyfy\Vendor\Model\VendorFactory;

class Export extends Action
{
    protected $vendors = [];
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
    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;
    /**
     * @var CountryFactory
     */
    protected $countryFactory;


    public function __construct(
        Context $context,
        Filesystem $filesystem,
        CollectionFactory $collectionFactory,
        StockRegistryInterface $stockRegistry,
        Vendor $vendor,
        VendorFactory $vendorFactory,
        FileFactory $fileFactory,
        OrderCollectionFactory $orderCollectionFactory,
        ProductRepository $productRepository,
        CountryFactory $countryFactory
    )
    {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->collectionFactory = $collectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->vendor = $vendor;
        $this->vendorFactory = $vendorFactory;
        $this->fileFactory = $fileFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->productRepository = $productRepository;
        $this->countryFactory = $countryFactory;
        parent::__construct($context);
    }

    public function getHeader()
    {
        return [
            'ID',
            'Vendors',
            'Status',
            'Items SKU',
            'Items Name',
            'Options',
            'Ship From Country',
            'Ship To Country',
            'Shipping Address',
            'Billing Address',
            'Shipping Information',
            'Customer Name',
            'Customer Email',
            'Customer Phone Number',
            'Payment Method',
            'Price (US$)'
        ];
    }

    public function execute()
    {
        $file = 'export/sales_order_' . md5(microtime()) . '.csv';
        $selected = $this->getRequest()->getParam('selected');
        $orderCollection = $this->orderCollectionFactory->create();
        if ($selected !== 'false' && is_array($selected)) {
            $orderCollection->addFieldToFilter('entity_id', ['in' => $selected]);
        }
        $orderCollection->setOrder('entity_id', 'DESC');
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->getHeader());
        foreach ($orderCollection as $order) {
            foreach ($order->getItems() as $item) {
                $rowData = $this->getRowData($order, $item);
                $stream->writeCsv($rowData);
            }
            $rowTotal = $this->getRowTotal($order);
            $stream->writeCsv($rowTotal);
            $stream->writeCsv([]);
        }
        $stream->unlock();
        $stream->close();
        $csvFiles = [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
        $fileName = 'sales_order_' . date('ymd') . '.csv';
        return $this->fileFactory->create($fileName, $csvFiles, DirectoryList::VAR_DIR);
    }

    public function getRowData($order, $item)
    {
        $vendor = $this->getVendor($item->getVendorId());
        $shippingMethod = json_decode($order->getShippingMethod(), true);
        $shippingAddress = $order->getShippingAddress();

        return [
            'id' => $order->getIncrementId(),
            'vendor' => $vendor->getId() ? $vendor->getName() : '',
            'status' => $order->getStatus(),
            'item_sku' => $item->getSku(),
            'item_name' => $item->getName(),
            'options' => $this->getItemOptions($item),
            'from' => $this->getShipFromCountry($item->getProductId()),
            'to' => $this->getCountry($shippingAddress->getCountryId())->getName(),
            'shipping_address' => $this->getAddress($shippingAddress),
            'bill_address' => $this->getAddress($order->getBillingAddress()),
            'ship_info' => isset($shippingMethod[$item->getLocationId()]) ? $shippingMethod[$item->getLocationId()] : '',
            'customer_name' => $order->getCustomerName(),
            'customer_email' => $order->getCustomerEmail(),
            'customer_phone' => $shippingAddress->getTelephone() ? $shippingAddress->getTelephone() : '',
            'payment' => $order->getPayment()->getMethodInstance()->getTitle(),
            'price' => $item->getPrice()
        ];
    }

    public function getVendor($vendorId)
    {
        if (!isset($this->vendors[$vendorId])) {
            $vendor = $this->vendorFactory->create()->load($vendorId);
            $this->vendors[$vendorId] = $vendor;
        }
        return $this->vendors[$vendorId];
    }

    protected function getItemOptions($item)
    {
        $itemOptions = [];
        if (isset($item->getProductOptions()['attributes_info'])) {
            foreach ($item->getProductOptions()['attributes_info'] as $option) {
                $itemOptions[] = $option['label'] . '(' . $option['value'] . ')';
            }
        }
        return implode(', ', $itemOptions);
    }

    protected function getAddress($address)
    {
        return implode(', ', $address->getStreet()) . ', ' . $address->getCity();
    }

    protected function getCountry($code)
    {
        return $this->countryFactory->create()->loadByCode($code);
    }

    protected function getShipFromCountry($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
            $shipFromCountry = $product->getShipFromCountry();
            return $this->getCountry($shipFromCountry)->getName();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    protected function getRowTotal($order)
    {
        return [
            'Subtotal: US$' . $order->getSubtotal(),
            'Shipping Cost: US$' . $order->getShippingAmount(),
            'Grand Total: US$' . $order->getGrandTotal(),
            'Total Paid: US$' . (float)$order->getTotalPaid(),
            'Total Refunded: US$' . (float)$order->getTotalRefunded(),
            'Total Due: US$' . $order->getTotalDue()
        ];
    }
}
