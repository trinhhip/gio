<?php

namespace Omnyfy\Enquiry\Block\Customer;

use Magento\Framework\View\Element\Template;

class History extends Template {

    /**
     * Enquiry collection
     *
     * @var \Omnyfy\Enquiry\Model\ResourceModel\Enquiries\Collection
     */
    protected $_enquiryCollection = null;

    /**
     * Enquiry factory
     *
     * @var \Omnyfy\Enquiry\Model\EnquiriesFactory
     */
    protected $_enquirymodelFactory;
	
    /**
     * Enquiry factory
     *
     * @var \Omnyfy\Enquiry\Model\ProductFactory
     */
    protected $_product;

    protected $_date;
	
	/**
     * @var \Magento\Customer\Model\Session
     */
    protected $_myCustomerSession;

    public function __construct(
		Template\Context $context, 
		\Omnyfy\Enquiry\Model\ResourceModel\Enquiries\CollectionFactory $enquirymodelFactory, 
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Catalog\Model\ProductFactory $product,
		array $data = []
    ) {

        parent::__construct($context, $data);
        $this->_enquirymodelFactory = $enquirymodelFactory;
		$this->_product = $product;
		$this->_date = $date;
		$this->_myCustomerSession = $customerSession;
        $this->_isScopePrivate = true;
    }

    public function _getCollection() {
        $collection = $this->_enquirymodelFactory->create();
        return $collection;
    }

    /**
     * Retrieve prepared Enquiries collection
     *
     * @return Omnyfy_Enquiry_Model_Resource_Enquiries_Collection
     */
    public function getCollection() {
        if (is_null($this->_enquiryCollection)) {

            $this->_enquiryCollection = $this->_getCollection()
                    ->addFieldToSelect('*')
                    ->join(
						array('enquiry_message' => 'omnyfy_enquiry_enquiry_messages'),
							'main_table.enquiries_id = enquiry_message.enquiry_id',
							array('message' => 'message', 'is_visible_frontend' => 'is_visible_frontend', 'enquiry_messages_id' => 'enquiry_messages_id', 'message_status' => 'status')
						)
					->join(
						array('vendor' => 'omnyfy_vendor_vendor_entity'),
							'main_table.vendor_id = vendor.entity_id',
							array('name' => 'name')
						);

            $this->_enquiryCollection->addFieldToFilter('customer_id', ['eq' => $this->getUserId()]);
            $this->_enquiryCollection->addFieldToFilter('main_table.status', array('in' => array(1,2,3)));

            /* $this->_enquiryCollection->setCurPage($this->getCurrentPage());
            $this->_enquiryCollection->setPageSize($this->_dataHelper->getEventsPerPage()); */
            $this->_enquiryCollection->setOrder('enquiry_message.is_visible_frontend', '1');
            $this->_enquiryCollection->setOrder('enquiry_message.status', '1');
            #$this->_enquiryCollection->setOrder('enquiry_message.enquiry_messages_id', 'ASC');
            $this->_enquiryCollection->setOrder('enquiries_id', 'desc');
            $this->_enquiryCollection->setOrder('updated_date', 'desc');
            $this->_enquiryCollection->getSelect()->group('main_table.enquiries_id');
        }

        return $this->_enquiryCollection;
    }

    protected function _prepareLayout() {
        $collection = $this->getCollection();

        parent::_prepareLayout();
        if ($collection) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'enquiries.pager');
            // assign collection to pager
            $pager->setLimit(100)->setCollection($collection);
            #$pager->setAvailableLimit([9 => 9, 18 => 18, 27 => 27, 81 => 81]);
            $pager->setAvailableLimit([100 => 100, 200 => 200, 300 => 300, 400 => 400]);
            $this->setChild('pager', $pager); // set pager block in layout
        }
        return $this;
    }

    /**
     * @return string
     */
    // method for get pager html
    public function getPagerHtml() {
        return $this->getChildHtml('pager');
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
	
	public function getEnquiryUrl($id) {
        return $this->getUrl('enquiry/customer/view', ['id' => $id]);
    }
	
	public function getFrontendDate($date){
		return $this->_date->date("D d M, Y",$date);
	}
}