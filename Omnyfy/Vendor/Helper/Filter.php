<?php


namespace Omnyfy\Vendor\Helper;


use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;

class Filter extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    private $amastyShopby;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;
    /**
     * @var \Omnyfy\Vendor\Model\VendorRepository
     */
    private $vendorRepository;
    /**
     * @var \Omnyfy\Vendor\Model\VendorTypeRepository
     */
    private $vendorTypeRepository;

    private $request;

    /**
     * Filter constructor.
     * @param Context $context
     * @param Registry $registry
     * @param \Amasty\Shopby\Helper\Data $amastyShopby
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Amasty\Shopby\Helper\Data $amastyShopby,
        \Magento\Framework\UrlInterface $urlInterface,
        \Omnyfy\Vendor\Model\VendorRepository $vendorRepository,
        \Omnyfy\Vendor\Model\VendorTypeRepository $vendorTypeRepository,
        \Magento\Framework\App\Request\Http $request
    )
    {
        parent::__construct($context);
        $this->registry = $registry;
        $this->amastyShopby = $amastyShopby;
        $this->urlInterface = $urlInterface;
        $this->vendorRepository = $vendorRepository;
        $this->vendorTypeRepository = $vendorTypeRepository;
        $this->request = $request;
    }

    public function getVendorLogoUrl(){
        if ($this->registry->registry('vendor_logo')) {
            return $this->registry->registry('vendor_logo');
        } else {
            return false;
        }
    }

    public function getVendorName(){
        return $this->registry->registry('vendor_name');
    }

    public function getVendorSeoSuffix($vendorId): string
    {
        return $this->urlInterface->getUrl().$this->amastyShopby->getAllProductsUrlKey()."?vendor_id={$vendorId}";
    }

    // Check if we should show the vendor link on product pages
    public function getValueOfHideVendorLinkFromVendorType($vendorId) {
        // if controller is product page view, run logic
        $moduleName = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();

        if ($moduleName == 'catalog' && $controller == 'product' && $action == 'view') {
            $vendor = $this->vendorRepository->getById($vendorId);
            // datascope in omnyfy_vendor_vendor_type_form
            if ($this->vendorTypeRepository->getById($vendor->getTypeId())->getHideVendorLinkOnProduct() == 0) {
                return false;
            }
        }
        return true;
    }
}
