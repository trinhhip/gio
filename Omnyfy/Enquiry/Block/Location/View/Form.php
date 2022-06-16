<?php
/**
 * Project: Enquiry M2.
 * User: abhay
 * Date: 15/3/18
 * Time: 3:54 PM
 */
namespace Omnyfy\Enquiry\Block\Location\View;
use Magento\Framework\View\Element\Template;

class Form extends \Magento\Framework\View\Element\Template{
	
	 public function __construct(
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\App\Response\RedirectInterface $redirect,
		\Omnyfy\Vendor\Model\LocationFactory $locationFactory,
		\Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        Template\Context $context,
        array $data = [])
    {
		$this->customerSession = $customerSession;
		$this->locationFactory = $locationFactory;
		$this->vendorFactory = $vendorFactory;
		$this->redirect = $redirect;
        parent::__construct($context, $data);
    }
	
	/**
	* Returns action url for contact form. Form submit URL
	*
	* @return string
	*/
	public function getFormAction(){
		return $this->getUrl('enquiry/index/enquiry', ['_secure' => true]);
	}
	
	public function isLoggedIn() {
        return $this->customerSession->isLoggedIn();
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
	
	/**
     * Return login url for guest users with referer url
     *
     * @return string
     */
    public function getRedirectedUrl() {
        return $this->redirect->getRedirectUrl();
    }
	
	public function isFormActive(){
		$locationId = $this->getRequest()->getParam('id');
		$locationData = $this->locationFactory->create()->load($locationId);
		
		$vendorData = $this->vendorFactory->create()->load($locationData->getVendorId());
		if($vendorData->getEnquiryForVendor()){
			return true;
		}
		return false;
	}
}