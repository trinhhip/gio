<?php
/**
 * Created by CIPL.
 * User: Abhay
 * Date: 24/5/2018
 * Time: 3:0 PM
 */

namespace Omnyfy\Enquiry\Controller\Customer;

use Magento\Framework\Controller\ResultFactory;
use Omnyfy\Enquiry\Helper\Data;

class Comment extends \Magento\Framework\App\Action\Action
{
    protected $dataPersistor;

    protected $_enquiryData;

    protected $_enquiriesCollectionFactory;

    protected $_date;

    protected $_customerSession;

    protected $_backendUrl;

    protected $_product;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Omnyfy\Enquiry\Helper\Data $enquiryData,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Catalog\Model\ProductFactory $product,
        \Omnyfy\Enquiry\Model\Enquiries $enquiriesCollectionFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_enquiryData  = $enquiryData;
		$this->_date = $date;
		$this->_customerSession = $customerSession;
        $this->_enquiriesCollectionFactory = $enquiriesCollectionFactory;
		$this->_product = $product->create();
        parent::__construct($context);
        $this->_backendUrl = $context->getBackendUrl();
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		if (!$this->_customerSession->isLoggedIn()) {
            $this->_redirect('customer/account');
            return;
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $__notifyCustomer = 0;
        $__VisibleFrontEnd= 0;

        $currentTime = $this->_enquiryData->getCurrentDateTime();
        $enquiryId   = $this->getRequest()->getParam('enquiryId');
        $new_message = $this->getRequest()->getParam('message');

        try {
            $data = array(
                "enquiry_id"            => $enquiryId,
                "from_id"               => $this->getRequest()->getParam('customerId'),
                "from_type"             => "customer",
                "to_id"                 => $this->getRequest()->getParam('vendorId'),
                "to_type"               => "vendor",
                "message"               => $new_message,
                "send_time"             => $currentTime,
                "is_notify_customer"    => $__notifyCustomer,
                "is_visible_frontend"   => $__VisibleFrontEnd,
                "status"                => 1
            );

            $model = $this->_objectManager->create('Omnyfy\Enquiry\Model\EnquiryMessages');
            $model->setData($data);
            $model->save();

            $enquiry = $this->_enquiriesCollectionFactory->load($enquiryId);
            $enquiry->setUpdatedDate($currentTime);
            $enquiry->save();


			$vendorId = $enquiry->getVendorId();

			$enquiry_history = "Message History";
			$userDashboad_link = $this->_enquiryData->getDashboardUrl();
			$fromEmail = array(
				"email" => $enquiry->getCustomerEmail(),
				"name" => $enquiry->getCustomerFirstName() . " " . $enquiry->getCustomerLastName()
			);
			$toEmail = array(
				"email" => $this->_enquiryData->getVendorEmail($vendorId),
				"name" => $this->_enquiryData->getVendorName($vendorId)
			);

			if($enquiry->getVendorId() && $enquiry->getProductId()){
				$serviceName = $this->_product->load($enquiry->getProductId())->getName();
			}else {
				$serviceName = $this->_enquiryData->getVendorName($vendorId);
			}

			$vars = [
				'customer' => $this->getCustomerName(),
				'service_name' => $serviceName,
				'vendor_name' => $this->_enquiryData->getVendorName($vendorId),
				'enquiry_link' => $this->adminEnquiryUrl(),
                'message'     => $new_message,
			];

			$this->_enquiryData->sendMessageToVendor($vars, $toEmail, $fromEmail);

            return $resultJson->setData([
                "message" => __('Sent the reply successfully'),
                "type" => "success",
                "title" => "I replied on ".$this->_date->date("D d M, Y",$currentTime),
                "enquiry_message" => $this->getRequest()->getParam('message'),
                "last-updated" => $currentTime
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                "message" => __('Error:%1', $e->getMessage()),
                "type" => "error"
            ]);
        }
    }

	public function adminEnquiryUrl() {
        return $this->_backendUrl->getUrl('omnyfy_enquiry/enquiries/index');
    }

	public function userLoggedDetails() {
        return $this->_customerSession->getCustomer();
    }

    public function getUserId() {
        return $this->userLoggedDetails()->getId();
    }

    public function getCustomerName() {
        return $this->userLoggedDetails()->getFirstname() . ' ' . $this->userLoggedDetails()->getLastname();
    }
}
