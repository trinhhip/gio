<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 5/24/2018
 * Time: 5:17 PM
 */

namespace Omnyfy\Enquiry\Controller\Enquiry;


class Save extends \Magento\Framework\App\Action\Action
{
    private $_enquiries;
    private $_enquiriesRepository;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Omnyfy\Enquiry\Model\Enquiries $enquiries,
        \Omnyfy\Enquiry\Model\EnquiriesRepository $enquiriesRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->_enquiries = $enquiries;
        $this->_enquiriesRepository = $enquiriesRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $firstName = $this->getRequest()->getParam('firstName');
            $lastName = $this->getRequest()->getParam('lastName');
            $email = $this->getRequest()->getParam('email');
            $mobile = $this->getRequest()->getParam('mobile');
            $company = $this->getRequest()->getParam('company');
            $message = $this->getRequest()->getParam('message');
            $vendorId = $this->getRequest()->getParam('vendorId');
            $productId = $this->getRequest()->getParam('productId');
            $customerId = $this->getRequest()->getParam('customerId');
            $storeId = $this->getRequest()->getParam('storeId');

            $enquiriesData = array(
                "vendor_id" => $vendorId,
                "product_id" => $productId,
                "customer_id" => $customerId,
                "customer_first_name" => $firstName,
                "customer_last_name" => $lastName,
                "customer_email" => $email,
                "customer_mobile" => $mobile,
                "customer_company" => $company,
                "status" => 2,
                "store_id" => $storeId,
                "summery" => ""
            );

            $messageData = array(
                "from_id" => $customerId,
                "from_type" => "customer",
                "to_id" => $vendorId,
                "to_type" => "vendor",
                "message" => $message,
                "is_notify_customer" => 1,
                "is_visible_frontend" => 1,
                "status" => 1
            );

            $enquiry = $this->_enquiries->setData($enquiriesData);

            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();

            try {
                $this->_enquiriesRepository->save($enquiry, $messageData);
                $response = ['success' => 'true'];
                $resultJson->setData($response);
            } catch (\Exception $e) {
                $response = ['success' => 'false'];
                $resultJson->setData($response);
            }

            return $resultJson;


        } catch (\Exception $exception) {
            
        }
    }
}