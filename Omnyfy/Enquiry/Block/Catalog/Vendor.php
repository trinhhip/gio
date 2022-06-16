<?php
/**
 * Project: Enquiry M2.
 * User: Abhay
 * Date: 4/4/18
 * Time: 10:38 AM
 */
namespace Omnyfy\Enquiry\Block\Catalog;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Vendor extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry;

    protected $vendorFactory;
    protected $inventoryFactory;
    protected $locationFactory;

    protected $redirect;
    protected $customerSession;

    protected $_helper;
    protected $_vendorResource;

    protected $_productFactory;

    public function __construct(
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
		\Omnyfy\Vendor\Model\LocationFactory $locationFactory,
		\Omnyfy\Vendor\Model\InventoryFactory $inventoryFactory,
		\Magento\Framework\App\Response\RedirectInterface $redirect,
		\Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        Template\Context $context,
        \Omnyfy\Enquiry\Helper\Data $helper,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = [])
    {
        $this->locationFactory = $locationFactory;
        $this->inventoryFactory = $inventoryFactory;
		$this->redirect = $redirect;
        $this->vendorFactory = $vendorFactory;
		$this->customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        $this->_helper = $helper;
        $this->_vendorResource = $vendorResource;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    public function isFormActive() {
        $productId = $this->getProductId();
        $vendorId = $this->getVendorId($productId);
        $vendor = $this->vendorFactory->create()->load($vendorId);
        if($vendor->getEnquiryForVendor()){
			return true;
		}
		return false;
    }

    public function getProduct() {
        $product = $this->_productFactory->create()->load($this->getProductId());
        if (!$product->getId()) {
            return false;
        }
        return $product;
    }

    public function getProductId() {
        return (int)$this->getRequest()->getParam('id');
    }

    public function getVendorId($productId)
    {
        if (empty($productId)) {
            //load vendor Id from parameter
            $vendorId = $this->getRequest()->getParam('vendor_id');
            $vendorId = empty($vendorId) ? $this->getRequest()->getParam('id') : $vendorId;
            return $vendorId;
        }
        return $this->_vendorResource->getVendorIdByProductId($productId);
    }

	public function getVendor()
    {
		$productId = $this->getProductId();
		$vendorId = $this->getVendorId($productId);

		$vendorCollection = $this->vendorFactory->create()->getCollection();
        $vendorCollection->addIdFilter($vendorId);
        return $vendorCollection->getFirstItem()->getData();
    }

   /*

    public function getProductsCount()
    {
        //TODO: retrieve products count by vendor_id and website_id
        return 40;
    } */

    public function getLogoUrl()
    {

		$vendor = $this->getVendor();
		$logo = $vendor[0]['logo'];
		if (empty($logo)) {
			return false;
		}
		//format url
		$logo = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $logo;

        return $logo;
    }

    public function getName()
    {
		$vendor = $this->getVendor();
		$name = $vendor[0]['name'];
		if (empty($name)) {
			return false;
		}

        return $name;
    }

	/**
     * Return login url for guest users with referer url
     *
     * @return string
     */
    public function getLoginUrl() {
        $url = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $login_url = $this->getUrl('customer/account/login', array('referer' => base64_encode($url)));
        return $login_url;
    }

	public function isLoggedIn() {
        return $this->customerSession->isLoggedIn();
    }

	/**
	* Returns action url for contact form. Form submit URL
	*
	* @return string
	*/
	public function getFormAction(){
		return $this->getUrl('enquiry/index/enquiry', ['_secure' => true]);
	}

	public function getLocationUrl(){
		$productId = $this->getProductId();
		$location = $this->_helper->getLocationByProductId($productId);
		if (empty($location) || !$location->getStatus()) {
		    return 'javascript:void(0)';
        }

		return $this->getUrl('vendor/index/location', ['id' => $location->getId()]);
	}

	public function isAnyLocation(){
		$productId = $this->getProductId();
		$location = $this->_helper->getLocationByProductId($productId);
		if (empty($location)) {
		    return false;
        }

		if ($location->getStatus()) {
		    return true;
        }

		return false;
	}

	public function isAnySource() {
        $productId = $this->getProductId();
        $source = $this->_helper->getSourceByProductId($productId);
        if ($source) {
            return true;
        }

        return false;
    }

	/**
     * Return login url for guest users with referer url
     *
     * @return string
     */
    public function getRedirectedUrl() {
        return $this->redirect->getRedirectUrl();
    }

	public function isProductEnquiryActive($vendorId, $productId){
        return $this->_helper->isEnabled($vendorId)
            && $this->isAnySource()
            && $this->_helper->isProductEnabled($vendorId, $productId);
	}
}
