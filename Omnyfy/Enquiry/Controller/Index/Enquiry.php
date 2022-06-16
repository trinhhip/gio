<?php

/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Omnyfy\Enquiry\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\ScopeInterface;
use Omnyfy\Enquiry\Model\Enquiries;
use Omnyfy\Enquiry\Helper\Data;
use Omnyfy\Enquiry\Model\EnquiryMessages;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Controller\ResultFactory;

class Enquiry extends Action {

    private $dataPersistor;

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    protected $context;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Enquiries
     */
    protected $_enquiries;

    /**
     * @var Data
     */
    protected $_enquiryData;

    /**
     * @var EnquiryMessages
     */
    protected $_enquiryMessages;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_myCustomerSession;

    /**
     * @var \Omnyfy\Vendor\Model\LocationFactory
     */
    protected $locationFactory;

    /**
     * @var \Omnyfy\Vendor\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var
     */
    protected $_product;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $_resultRedirect;

    const XML_PATH = 'enquiry/';

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Filesystem $fileSystem,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Omnyfy\Vendor\Model\LocationFactory $locationFactory,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Enquiries $enquiries,
        Data $enquiryData,
        EnquiryMessages $enquiryMessages,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $product,
        UrlInterface $backendUrl,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Controller\ResultFactory $result
    ) {
        parent::__construct($context);
        $this->fileSystem = $fileSystem;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_storeManager = $storeManager;
        $this->locationFactory = $locationFactory;
        $this->_enquiries = $enquiries;
        $this->vendorFactory = $vendorFactory;
        $this->_enquiryData = $enquiryData;
        $this->_enquiryMessages = $enquiryMessages;
        $this->scopeConfig = $scopeConfig;
        $this->_myCustomerSession = $customerSession;
        $this->_product = $product->create();
        $this->_backendUrl = $backendUrl;
        $this->_logger = $logger;
        $this->_resultRedirect = $result;
    }

    public function execute() {
        $post = $this->getRequest()->getPostValue();
        $serviceName = '';
        if (isset($post['location_id'])) {
            $locationId = $post['location_id'];
            $locationData = $this->locationFactory->create()->load($locationId);
            $vendorId = $locationData->getVendorId();
            $subject = 'enquiry from location page';
            $serviceName = $locationData->getLocationName();
        }

        if (isset($post['vendor_id'])) {
            $vendorId = $post['vendor_id'];
            $subject = 'enquiry from product page';
        }

        $vendorData = $this->vendorFactory->create()->load($vendorId);
        $vendorName = $vendorData->getName();
        $vendorEmail = $vendorData->getEmail();

        /** Save Enquiry Data * */
        $currentTime = $this->_enquiryData->getCurrentDateTime();

        $productId = NULL;
        if (isset($post['product_id'])) {
            $productId = $post['product_id'];
            $serviceName = $this->_product->load($productId)->getName();
        }
        $customerId = ($this->getUserId()) ? $this->getUserId() : NULL;

        try {
            $data = array(
                "vendor_id" => $vendorId,
                "product_id" => $productId,
                "customer_id" => $customerId,
                "customer_first_name" => $post['firstname'],
                "customer_last_name" => $post['lastname'],
                "customer_email" => $post['emailaddress'],
                "customer_mobile" => $post['mobilenumber'],
                "customer_company" => $post['company'],
                "message" => $post['message'],
                "summary" => $post['summary'],
                "created_date" => $currentTime,
                "updated_date" => $currentTime,
                "status" => \Omnyfy\Enquiry\Model\Enquiries\Source\Status::NEW_MESSAGE,
                "store_id" => $this->_storeManager->getStore()->getId()
            );

            $__enquiryId = $this->saveEnquiry($data);

            if ($__enquiryId) {
                $messageData = array(
                    "enquiry_id" => $__enquiryId,
                    "from_id" => $this->getUserId(),
                    "from_type" => 'customer',
                    "to_id" => $vendorId,
                    "to_type" => 'vendor',
                    "message" => $post["message"],
                    "send_time" => $currentTime,
                    "is_notify_customer" => '1',
                    "is_visible_frontend" => '1',
                    "status" => 1
                );

                $this->saveEnquiryMessage($messageData);
            }
        } catch (\Exception $exception) {
            //$this->_logger->debug("Error Saving the message". $exception->getMessage());
        }
        /** Save Enquiry Data end * */

        //Send to vendor
        $toEmail = array(
            "email" => trim($vendorEmail),
            "name" => $vendorName
        );

        $templateId = $this->getGeneralConfig(Data::ENQUIRY_VENDOR_TEMPLATE, $this->_storeManager->getStore()->getId());

        $customerVar = [
            'customer' => $this->getCustomerName(),
            'service_name' => $serviceName,
            'vendor_name' => $vendorName,
            'message' => $post["message"],
            'admin_dashboard_link' => $this->adminEnquiryUrl(),
        ];
        $from = $this->getSenderFrom();
        $cc = $this->_enquiryData->getEnquiryVendorCc();
        $this->_enquiryData->sendEmail($templateId, $customerVar, $toEmail, $from, $cc);

        // Send email customer
        $templateId = $this->getGeneralConfig(Data::ENQUIRY_CUSTOMER_TEMPLATE, $this->_storeManager->getStore()->getId());
        $userDashboad_link = $this->_enquiryData->getDashboardUrl();
        $customerVar = [
            "customer_first_name" => $post['firstname'],
            "enquiry_link" => $userDashboad_link,
        ];
        $cc = $this->_enquiryData->getEnquiryCustomerCc();
        $from = $this->_enquiryData->getEnquiryCustomerFrom();
        $toEmail = array(
            "email" => $post['emailaddress'],
            "name" => $post['firstname']." ".$post['lastname'],
        );
        $this->_enquiryData->sendEmail($templateId, $customerVar, $toEmail, $from, $cc);

        $this->messageManager->addComplexSuccessMessage('submitEnquiry');
        $resultRedirect = $this->_resultRedirect->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
        //$this->_redirect($this->_redirect->getRefererUrl());
    }

    public function userLoggedDetails() {
        return $this->_myCustomerSession->getCustomer();
    }

    public function getUserId() {
        return $this->userLoggedDetails()->getId();
    }

    public function saveEnquiry($enquiryData) {
        $this->_enquiries->setData($enquiryData);
        $enquiry = $this->_enquiries->save();
        return $enquiry->getEnquiriesId();
    }

    public function saveEnquiryMessage($messageData) {
        $this->_enquiryMessages->setData($messageData);
        $this->_enquiryMessages->save();
    }

    public function getCustomerName() {
        return $this->userLoggedDetails()->getFirstname() . ' ' . $this->userLoggedDetails()->getLastname();
    }
    public function adminEnquiryUrl() {
        return $this->_backendUrl->getUrl('omnyfy_enquiry/enquiries/index');
    }
    public function getSenderFrom($sentFrom = 'support') {
        $sender = [];
        $sender['email'] = trim($this->scopeConfig->getValue('trans_email/ident_' . $sentFrom . '/email', ScopeInterface::SCOPE_STORE));
        $sender['name'] = $this->scopeConfig->getValue('trans_email/ident_' . $sentFrom . '/name', ScopeInterface::SCOPE_STORE);
        return $sender;
    }

    public function getGeneralConfig($code, $storeId = null) {
        return $this->getConfigValue($code, $storeId);
    }
    public function getConfigValue($field, $storeId = null) {
        return $this->scopeConfig->getValue(
                        $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

}
