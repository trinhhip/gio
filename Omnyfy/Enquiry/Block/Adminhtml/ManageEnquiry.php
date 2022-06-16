<?php


namespace Omnyfy\Enquiry\Block\Adminhtml;

class ManageEnquiry extends \Magento\Backend\Block\Widget\Form\Container
{
    private $_enquiryCollectionFactory;
    private $_enquiryMessageCollectionFactory;
    private $_enquiryData;
    private $_backendUrl;
    private $_status;

    public $enquiry     = array();
    public $messages    = array();
    public $replyAjaxUrl;
    public $emailAjaxUrl;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Omnyfy\Enquiry\Model\ResourceModel\Enquiries\CollectionFactory $enquiryCollectionFactory,
        \Omnyfy\Enquiry\Model\ResourceModel\EnquiryMessages\CollectionFactory $enquiryMessageCollectionFactory,
        \Omnyfy\Enquiry\Helper\Data $enquiryData,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $data = []
    ) {
        $this->_enquiryCollectionFactory = $enquiryCollectionFactory;
        $this->_enquiryMessageCollectionFactory = $enquiryMessageCollectionFactory;
        $this->_enquiryData = $enquiryData;
        $this->_backendUrl = $backendUrl;
        parent::__construct($context, $data);

        $this->getEnquiry();
        $this->replyAjaxUrl = $this->getReplyAjaxUrl();
        $this->emailAjaxUrl = $this->emailAjaxUrl();
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_objectId = 'message_form';
        $this->_blockGroup = 'Omnyfy_Enquiry';
        $this->_controller = 'adminhtml_enquiries';
        $this->buttonList->remove("save");
        $this->buttonList->remove("reset");
    }

    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'custom_action_list',
            'label' => __('Action'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getCustomActionListOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        parent::_prepareLayout();
        $this->getLayout()->getBlock('page.title')->setPageTitle("Manage Enquiries");
    }


    /**
     * Retrieve options for 'CustomActionList' split button
     *
     * @return array
     */
    protected function _getCustomActionListOptions()
    {
        if ($this->_status != \Omnyfy\Enquiry\Model\Enquiries\Source\Status::COMPLETE_MESSAGE) {
            $splitButtonOptions = [
                'archive' => ['label' => __('Delete'), 'onclick' => 'if(confirm("Are you sure you wan\'t to delete the record?")){ setLocation("' . $this->getArchiveUrl() . '")}else{ setLocation("'.$this->getSelfurl().'")}'],
                'new' => ['label' => __('Reopen'), 'onclick' => 'setLocation("' . $this->getUnreadUrl() . '")'],
                'complete' => ['label' => __('Complete'), 'onclick' => 'setLocation("' . $this->getCompleteUrl() . '")']
            ];
        } else {
            $splitButtonOptions = [
                'archive' => ['label' => __('Delete'), 'onclick' => 'if(confirm("Are you sure you wan\'t to delete the record?")){ setLocation("' . $this->getArchiveUrl() . '")}else{ setLocation("'.$this->getSelfurl().'")}'],
                'new' => ['label' => __('Reopen'), 'onclick' => 'setLocation("' . $this->getUnreadUrl() . '")']
            ];
        }

        return $splitButtonOptions;
    }

    public function getReplyAjaxUrl(){
        return $this->_backendUrl->getUrl('enquiries/messages/save');
    }

    public function emailAjaxUrl(){
        return $this->_backendUrl->getUrl('enquiries/messages/email');
    }



    private function getCompleteUrl()
    {
        return $this->getUrl('*/*/complete', ['enquiries_id' => $this->getEnquiryId()]);
    }

    private function getArchiveUrl() {
        return $this->getUrl('*/*/delete', ['enquiries_id' => $this->getEnquiryId()]);
    }

    private function getUnreadUrl() {
        return $this->getUrl('*/*/unread', ['enquiries_id' => $this->getEnquiryId()]);
    }

    private function getSelfurl(){
        return $this->getUrl('*/*/*', ['enquiries_id' => $this->getEnquiryId()]);
    }


    private function getEnquiryId(){
        return $this->getRequest()->getParam('enquiries_id');
    }

    public function getEnquiry()
    {
        $__enquiry = array();
        $enquiries_id = $this->getEnquiryId();
        $enquiries = $this->_enquiryCollectionFactory->create();
        $enquiries->addFilter('enquiries_id', ['eq' => $enquiries_id]);

        foreach($enquiries as $enquiry){
            $__enquiry['enquiry_id']            = $enquiry->getData('enquiries_id');
            $__enquiry['vendor_id']             = $enquiry->getData('vendor_id');
            $__enquiry['vendor_name']           = $this->_enquiryData->getVendorName($enquiry->getData('vendor_id'));
            $__enquiry['product_id']            = $enquiry->getData('product_id');
            $__enquiry['product_name']          = $this->_enquiryData->getProductName($enquiry->getData('product_id'));
            $__enquiry['customer_id']           = $enquiry->getData('customer_id');
            $__enquiry['customer_first_name']   = $enquiry->getData('customer_first_name');
            $__enquiry['customer_last_name']    = $enquiry->getData('customer_last_name');
            $__enquiry['customer_full_name']    = $enquiry->getData('customer_first_name')." ".$enquiry->getData('customer_last_name');
            $__enquiry['customer_type']         = ""; //**TODO** Confirm Customer Type
            $__enquiry['customer_email']        = $enquiry->getData('customer_email');
            $__enquiry['customer_mobile']       = $enquiry->getData('customer_mobile');
            $__enquiry['customer_company']      = $enquiry->getData('customer_company');
            $__enquiry['created_date']          = $enquiry->getData('created_date');
            $__enquiry['updated_date']          = $enquiry->getData('updated_date');
            $__enquiry['status']                = $enquiry->getData('status');
            $__enquiry['status_name']           = \Omnyfy\Enquiry\Model\Enquiries\Source\Status::getOptionArray()[$enquiry->getData('status')];
            $__enquiry['summery']               = $enquiry->getData('summary');

            if ($__enquiry['status'] == \Omnyfy\Enquiry\Model\Enquiries\Source\Status::NEW_MESSAGE ) {
                $enquiry->setStatus(\Omnyfy\Enquiry\Model\Enquiries\Source\Status::OPEN_MESSAGE);
                $enquiry->save();
            }
            $this->_status = $enquiry->getData('status');
        }
        $this->enquiry  = $__enquiry;
        $this->messages = $this->_enquiryData->getMessages($enquiries_id);

        return $this;
    }
}
