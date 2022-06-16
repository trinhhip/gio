<?php

namespace Omnyfy\Vendor\Observer;

use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AssignVendor implements ObserverInterface
{

    private $import;
    /**
     * @var Action
     */
    private $massAction;
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
     */
    protected $storeResolver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @param Action $massAction
     * @param \Omnyfy\Vendor\Helper\Backend $backendHelper
     * @param \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver
     * @param \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        Action $massAction,
        \Omnyfy\Vendor\Helper\Backend $backendHelper,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ProductRepository $productRepository
    )
    {
        $this->massAction = $massAction;
        $this->backendHelper = $backendHelper;
        $this->storeResolver = $storeResolver;
        $this->vendorResource = $vendorResource;
        $this->logger = $logger;
        $this->_productRepository = $productRepository;
    }

    public function execute(Observer $observer)
    {
        $this->import = $observer->getEvent()->getAdapter();
        //Request For Quote
        $data = array();
        try {
            if (($products = $observer->getEvent()->getBunch()) && $this->getVendorId()) {
                foreach ($products as $product){
                    $productData = $this->getProductBySku($product['sku']);
                    $this->vendorResource->saveProductRelation(['product_id' => $productData->getId(), 'vendor_id' => $this->getVendorId()]);
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @param $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku);
    }

    public function getVendorId(){
        return $this->backendHelper->getBackendVendorId();
    }
}
