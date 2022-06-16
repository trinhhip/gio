<?php


namespace OmnyfyCustomzation\Vendor\Block\Product\View;


use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Omnyfy\Vendor\Model\VendorFactory;
use OmnyfyCustomzation\Vendor\Helper\Url;

class Vendor extends Template
{
    /**
     * @var Product
     */
    protected $product = null;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;
    /**
     * @var VendorFactory
     */
    protected $vendorFactory;
    /**
     * @var Url
     */
    public $vendorUrl;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param VendorFactory $vendorFactory
     * @param Url $vendorUrl
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        VendorFactory $vendorFactory,
        Url $vendorUrl,
        array $data = []
    )
    {
        $this->coreRegistry = $registry;
        $this->vendorFactory = $vendorFactory;
        $this->vendorUrl = $vendorUrl;
        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->product) {
            $this->product = $this->coreRegistry->registry('product');
        }
        return $this->product;
    }

    public function getVendor()
    {
        $product = $this->getProduct();
        $vendor = $this->vendorFactory->create();
        $vendorId = $vendor->getResource()->getVendorIdByProductId($product->getId());
        if (!$vendorId) {
            return false;
        }
        $vendor->load($vendorId);
        return $vendor;
    }
    public function getVendorUrl($vendor){
        return $this->vendorUrl->getVendorUrl($vendor);
    }
}
