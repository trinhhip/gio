<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 5/2/2018
 * Time: 5:41 PM
 */

namespace Omnyfy\Enquiry\Controller\Adminhtml\Messages;

use Magento\Framework\Controller\ResultFactory;
use Omnyfy\Enquiry\Helper\Data;

class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;

    protected $_enquiryData;

    protected $_enquiriesCollectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Omnyfy\Enquiry\Helper\Data $enquiryData,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Omnyfy\Enquiry\Model\Enquiries $enquiriesCollectionFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_enquiryData  = $enquiryData;
        $this->_enquiriesCollectionFactory = $enquiriesCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $__notifyCustomer = 0;
        $__VisibleFrontEnd= 0;

        if ($this->getRequest()->getParam('notifyCustomer'))
            $__notifyCustomer = 1;

        if ($this->getRequest()->getParam('visibleStorefront'))
            $__VisibleFrontEnd = 1;

        $currentTime = $this->_enquiryData->getCurrentDateTime();
        $enquiryId   = $this->getRequest()->getParam('enquiryId');
        $new_message = $this->getRequest()->getParam('message');

        try {
            $data = array(
                "enquiry_id"            => $enquiryId,
                "from_id"               => $this->getRequest()->getParam('vendorId'),
                "from_type"             => "vendor",
                "to_id"                 => $this->getRequest()->getParam('customerId'),
                "to_type"               => "customer",
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

            if ($__notifyCustomer) {
                $vendorId = $enquiry->getVendorId();

                $enquiry_history = "Message History";
                $userDashboad_link = $this->_enquiryData->getDashboardUrl();
                $toEmail = array(
                    "email" => $enquiry->getCustomerEmail(),
                    "name" => $enquiry->getCustomerFirstName() . " " . $enquiry->getCustomerLastName()
                );


                $vars = array(
                    "customer" => $enquiry->getCustomerFirstName() . " " . $enquiry->getCustomerLastName(),
                    "customer_first_name" => $enquiry->getCustomerFirstName(),
                    "vendor" => "",
                    "enquiry_id" => $enquiry->getId(),
                    "new_message" => $new_message,
                    "enquiry_history" => $enquiry_history,
                    "enquiry_link" => $userDashboad_link,
                    "service_name" => $this->getRequest()->getParam('productName')
                );

                $this->_enquiryData->sendMessageToCustomer($vars, $toEmail, $vendorId);
                $title = "Customer Notified ";
            }else {
                $title = "Customer Not Notified ";
            }
            return $resultJson->setData([
                "message" => __('Sent the reply successfully'),
                "type" => "success",
                "title" => $title." | ".$currentTime,
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
}