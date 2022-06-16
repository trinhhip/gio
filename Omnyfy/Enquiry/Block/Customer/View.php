<?php

namespace Omnyfy\Enquiry\Block\Customer;

use Magento\Framework\View\Element\Template;

class View extends Template {
	
    /**
     * Enquiry factory
     *
     * @var \Omnyfy\Enquiry\Model\ProductFactory
     */
    protected $_product;
	
	private $_enquiryData;
	
	protected $vendorRepository;
	protected $_enquiryMessageCollectionFactory;

	protected $_locationFactory;

	protected $_date;

	protected $coreRegistry;
	
	/**
     * @var \Magento\Customer\Model\Session
     */
    protected $_myCustomerSession;

    public function __construct(
		Template\Context $context, 
		\Omnyfy\Vendor\Api\VendorRepositoryInterface $vendorRepository,
		\Omnyfy\Enquiry\Helper\Data $enquiryData,
		\Omnyfy\Vendor\Model\LocationFactory $locationFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Omnyfy\Enquiry\Model\ResourceModel\EnquiryMessages\CollectionFactory $enquiryMessageCollectionFactory,
		\Magento\Catalog\Model\ProductFactory $product,
		\Magento\Framework\Registry $coreRegistry,
		array $data = []
    ) {

        parent::__construct($context, $data);
		$this->_product = $product;
		$this->_myCustomerSession = $customerSession;
		$this->_locationFactory = $locationFactory;
		$this->_enquiryMessageCollectionFactory = $enquiryMessageCollectionFactory;
		$this->_enquiryData = $enquiryData;
		$this->_date = $date;
		$this->coreRegistry = $coreRegistry;
		$this->vendorRepository = $vendorRepository;
        $this->_isScopePrivate = true;
    }
	
	/**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {

		$this->pageConfig->addBodyClass('cms-enquiry-view');
		$this->pageConfig->getTitle()->set($this->getEnquiry()->getSummary());
        return parent::_prepareLayout();
    }
	
	public function getEnquiry(){
		return $this->coreRegistry->registry('current_enquiry');
	}

    public function _getCollection() {
        $collection = $this->_enquirymodelFactory->create();
        return $collection;
    }
	
    public function userLoggedDetails() {
        return $this->_myCustomerSession->getCustomer();
    }

    public function getUserId() {
        return $this->_myCustomerSession->getCustomer()->getId();
    }

    /**
     * @return string 1 - New 2-Open 3-Complete 4-Achieve
     */
    public function getStatusLabel($statusCode) {

        $statusLabel = NULL;

        if ($statusCode == 1) {
            $statusLabel = 'New';
        } else if ($statusCode == 2) {
            $statusLabel = 'Open';
        } else if ($statusCode == 3) {
            $statusLabel = 'Complete';
        } else if ($statusCode == 0) {
            $statusLabel = 'Achieve';
        }

        return $statusLabel;
    }
	
	public function getProduct($productId)
    {
        return $this->_product->create()->load($productId);
    }
	
	public function getProductName($productId){
		return $this->getProduct($productId)->getName();
	}
	
	public function getVendor($vendorId){
		return $this->vendorRepository->getById($vendorId);
	}
	
	public function getLogoUrl($logo)
    {
		if (empty($logo)) {
			return false;
		}
		//format logo url
		$logo = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $logo;
        return $logo;
    }
	
	public function getMessages($enquiryId,$enquiryFirstMessageId=null,$index=null) {
        $__messagesArray = array();
        $messages = $this->_enquiryMessageCollectionFactory->create();
        $messages->addFieldToFilter('enquiry_id',['eq' => $enquiryId]);
		if($enquiryFirstMessageId){
			$messages->addFieldToFilter('enquiry_messages_id',['neq' => $enquiryFirstMessageId]);
		}	
        $messages->setOrder('enquiry_messages_id','ASC');
        if($index){
			$messages->setPageSize($index);
		}	

        foreach($messages as $message) {
            $__messagesArray[] = array (
                "enquiry_messages_id"   => $message->getData("enquiry_messages_id"),
                "from_id"               => $message->getData("from_id"),
                "from_type"             => $message->getData("from_type"),
                "to_id"                 => $message->getData("to_id"),
                "to_type"               => $message->getData("to_type"),
                "send_time"             => $message->getData("send_time"),
                "message"               => $message->getData("message"),
                "is_notify_customer"    => $message->getData("is_notify_customer"),
                "is_visible_frontend"   => $message->getData("is_visible_frontend"),
                "status"                => $message->getData("status")
            );
        }
        return $__messagesArray;
    }
	
	public function getReplyAjaxUrl(){
        return $this->getUrl('enquiry/customer/comment');
    }
	
	public function getFrontendDate($date){
		return $this->_date->date("D d M, Y",$date);
	}
	
	public function getLocationUrl($vendorId){
		$locationCollection = $this->_locationFactory->create()->getCollection()
													->addFieldToSelect('*')
													->addFieldToFilter('vendor_id', $vendorId)
													->addFieldToFilter('status','1');
		$locationId = $locationCollection->getFirstItem()->getEntityId();
		if($locationId){
			return $this->getUrl('booking/practice/view', ['id' => $locationId]);
		} else{
			return 'javascript:void(0);';
		}
		
	}
}