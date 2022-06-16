<?php

namespace Omnyfy\Enquiry\Helper;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const MANAGE_ENQUIRY_ENABLE = 'enquiry/general/enable';

    const ENQUIRY_VENDOR_TEMPLATE = 'enquiry/enquiry_vendor/template';
    const ENQUIRY_VENDOR_CC = 'enquiry/enquiry_vendor/cc';

    const ENQUIRY_CUSTOMER_TEMPLATE = 'enquiry/enquiry_customer/template';
    const ENQUIRY_CUSTOMER_CC = 'enquiry/enquiry_customer/cc';
    const ENQUIRY_CUSTOMER_FROM = 'enquiry/enquiry_customer/sent_from';

    const MESSAGE_VENDOR_TEMPLATE = 'enquiry/message_vendor/template';
    const MESSAGE_VENDOR_CC = 'enquiry/message_vendor/cc';

    const MESSAGE_CUSTOMER_TEMPLATE = 'enquiry/message_customer/template';
    const MESSAGE_CUSTOMER_FROM = 'enquiry/message_customer/sent_from';
    const MESSAGE_CUSTOMER_CC = 'enquiry/message_customer/cc';

    const CUSTOMER_TYPE = 'customer';
    const VENDOR_TYPE = 'vendor';

    /**
     * @var \Omnyfy\Vendor\Model\VendorFactory
     */
    private $_vendorFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $_customer;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $_products;

    /**
     * @var \Omnyfy\Enquiry\Model\ResourceModel\EnquiryMessages\CollectionFactory
     */
    private $_enquiryMessageCollectionFactory;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $_inlineTranslation;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $_timezone;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $_date;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $_transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var Email
     */
    private $_email;

    /**
     * @var \Magento\Framework\Url
     */
    private $_url;

    protected $locationCollectionFactory;

    protected $sourceCollectionFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Product $products
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Omnyfy\Vendor\Model\VendorFactory $vendorFactory
     * @param \Omnyfy\Enquiry\Model\ResourceModel\EnquiryMessages\CollectionFactory $enquiryMessageCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param Email $email
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Url $url
     * @param \Omnyfy\Vendor\Model\Resource\Location\CollectionFactory $locationCollectionFactory
     * @param \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Product $products,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Omnyfy\Enquiry\Model\ResourceModel\EnquiryMessages\CollectionFactory $enquiryMessageCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Omnyfy\Enquiry\Helper\Email $email,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Url $url,
        \Omnyfy\Vendor\Model\Resource\Location\CollectionFactory $locationCollectionFactory,
        \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory
    ) {
        $this->_vendorFactory       = $vendorFactory;
        $this->_products            = $products;
        $this->_customer            = $customerRepositoryInterface;
        $this->_enquiryMessageCollectionFactory = $enquiryMessageCollectionFactory;
        $this->_date                = $date;
        $this->_timezone            = $timezone;
        $this->_transportBuilder    = $transportBuilder;
        $this->_storeManager        = $storeManager;
        $this->_email               = $email;
        $this->_inlineTranslation   = $inlineTranslation;
        $this->_url                 = $url;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->sourceCollectionFactory = $sourceCollectionFactory;

        parent::__construct($context);
    }

    public function isEnabled($vendorId)
    {
        if (empty($vendorId)) {
            return false;
        }
        $isEnabled = $this->scopeConfig->getValue(
            self::MANAGE_ENQUIRY_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$isEnabled) {
            return $isEnabled;
        }
        try {
            $this->_eventManager->dispatch('omnyfy_enquiry_form_is_enabled', ['vendor_id' => $vendorId]);
        } catch (\Exception $e) {
            return false;
        }

        return $isEnabled;
    }
    /***/
    public function getEnquiryVendorTemplate()
    {
        return $this->scopeConfig->getValue(
            self::ENQUIRY_VENDOR_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getEnquiryVendorCc()
    {
        $data = $this->scopeConfig->getValue(
            self::ENQUIRY_VENDOR_CC,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    public function getEnquiryCustomerTemplate()
    {
        return $this->scopeConfig->getValue(
            self::ENQUIRY_CUSTOMER_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getEnquiryCustomerFrom()
    {
        return $this->scopeConfig->getValue(
            self::ENQUIRY_CUSTOMER_FROM,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getEnquiryCustomerCc()
    {
        return $this->scopeConfig->getValue(
            self::ENQUIRY_CUSTOMER_CC,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMessageVendorTemplate()
    {
        return $this->scopeConfig->getValue(
            self::MESSAGE_VENDOR_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMessageVendorCc()
    {
        return $this->scopeConfig->getValue(
            self::MESSAGE_VENDOR_CC,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMessageCustomerTemplate()
    {
        return $this->scopeConfig->getValue(
            self::MESSAGE_CUSTOMER_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMessageCustomerFrom()
    {
        return $this->scopeConfig->getValue(
            self::MESSAGE_CUSTOMER_FROM,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMessageCustomerCc()
    {
        return $this->scopeConfig->getValue(
            self::MESSAGE_CUSTOMER_CC,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function sendMessageToCustomer($vars, $sendEmail, $vendorId)
    {
        $this->_logger->debug("Send customer email");
        $from       = $this->getMessageCustomerFrom();
        $vendorName = $this->getVendorName($vendorId);
        $vendorEmail = $this->getVendorEmail($vendorId);
        if ($from == 'vendor') {
            $from = array(
                "email" => $vendorEmail,
                "name"  => $vendorName
            );
        }
        $vars["vendor"] = $this->getVendorName($vendorId);

        $templateId = $this->getMessageCustomerTemplate();
        $cc         = $this->getMessageCustomerCc();

        $this->sendEmail($templateId, $vars, $sendEmail, $from, $cc);
    }

    public function sendMessageToVendor($vars, $sendEmail, $from)
    {
        $templateId = $this->getMessageVendorTemplate();
        $cc         = $this->getMessageVendorCc();
        $this->_logger->debug("Send email to vendor");
        $this->sendEmail($templateId, $vars, $sendEmail, $from, $cc);
    }

    public function sendEnquiryToVendor($vars, $vendorId)
    {
        $vendorName = $this->getVendorName($vendorId);
        $vendorEmail = array(
            "email" => $this->getVendorEmail($vendorId),
            "name"  => $this->getVendorName($vendorId)
        );
        $vars['vendor_name'] = $vendorName;
        $from       = "general";
        $templateId = $this->getEnquiryVendorTemplate();
        $cc         = $this->getEnquiryVendorCc();

        $this->sendEmail($templateId, $vars, $vendorEmail, $from, $cc);
    }

    public function sendEnquiryToCustomer($vars, $sendEmail, $vendorId)
    {
        $from       = $this->getEnquiryCustomerFrom();
        $vendorName = $this->getVendorName($vendorId);
        $vendorEmail = $this->getVendorEmail($vendorId);

        if ($from == 'vendor') {
            $from = array(
                "email" => $vendorEmail,
                "name"  => $vendorName
            );
        }

        $vars["vendor"] = $this->getVendorName($vendorId);

        $templateId = $this->getEnquiryCustomerTemplate();
        $cc         = $this->getEnquiryCustomerCc();

        $this->sendEmail($templateId, $vars, $sendEmail, $from, $cc);
    }

    public function sendEmail($templateId, $vars, $sendEmail, $from, $cc = null)
    {
        $this->_inlineTranslation->suspend();
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateVars($vars)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId()
            ])
            ->setFrom($from)
            ->addTo($sendEmail["email"], $sendEmail["name"]);

        if ($cc) {
            $transport = $transport->addCc($cc);
        }

        $transport->getTransport()->sendMessage();
        $this->_inlineTranslation->resume();
    }

    public function isVendorEnabled($vendorId)
    {
        $vendorCollection = $this->_vendorFactory->create()->getCollection();
        $vendorCollection->addFieldToFilter('entity_id', ['eq', $vendorId]);
        $vendorCollection->addAttributeToSelect('enquiry_for_vendor');

        foreach ($vendorCollection as $vendor) {
            if ($vendor->getData('enquiry_for_vendor'))
                return true;
        }
        return false;
    }

    public function isVendorProductsEnabled($vendorId)
    {
        $vendorCollection = $this->_vendorFactory->create()->getCollection();
        $vendorCollection->addFieldToFilter('entity_id', ['eq', $vendorId]);
        $vendorCollection->addAttributeToSelect('enquiry_for_products');

        foreach ($vendorCollection as $vendor) {
            if ($vendor->getData('enquiry_for_products'))
                return true;
        }
        return false;
    }

    public function isProductEnabled($vendorId, $productId)
    {
        $__product = $this->getProduct($productId);
        $enable_enquiry = $__product->getResource()->getAttributeRawValue($productId, 'enable_enquiry', \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE);
        if ($enable_enquiry == 1) {
            return true;
        } else if ($enable_enquiry == -1) {
            if ($this->isVendorProductsEnabled($vendorId)) {
                return true;
            }
        }
        return false;
    }

    public function getUserDashboardUrl()
    {
        return $this->_urlBuilder->getUrl('enquiry/Index/Enquiry');
    }

    public function getDashboardUrl()
    {
        return $this->_url->getUrl('customer/account/login');
    }

    public function getVendor($id)
    {
        $vendorCollection = $this->_vendorFactory->create()->getCollection();
        $vendorCollection->addFieldToFilter('entity_id', ['eq', $id]);

        foreach ($vendorCollection as $vendor) {
            return $vendor;
        }
    }

    public function getVendorName($id)
    {
        $__vendor = $this->getVendor($id);
        return $__vendor->getData('name');
    }

    public function getVendorEmail($id)
    {
        $__vendor = $this->getVendor($id);
        return $__vendor->getData('email');
    }

    public function getProduct($id)
    {
        return $this->_products->load($id);
    }

    public function getProductName($id)
    {
        $__product = $this->getProduct($id);
        return $__product->getName();
    }

    public function getMessages($enquiry_id)
    {
        $__messagesArray = array();
        $messages = $this->_enquiryMessageCollectionFactory->create();
        $messages->addFilter('enquiry_id', ['eq' => $enquiry_id]);
        $messages->setOrder('send_time', 'ASC');

        foreach ($messages as $message) {
            $__messagesArray[] = array(
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

    public function getCurrentDateTime()
    {
        return $this->_timezone->date(new \DateTime($this->_date->gmtDate()))->format("Y-m-d H:i:s");
    }

    public function getFrontendTimeFormat($curTime)
    {
        return $this->_timezone->date(new \DateTime($curTime))->format("D d M, Y");
    }

    public function getLocationByProductId($productId)
    {
        $locationCollection = $this->locationCollectionFactory->create();
        //$locationCollection->addProductIdFilter($productId);

        $location = $locationCollection->getFirstItem();
        return empty($location->getId()) ? false : $location;
    }

    public function getSourceByProductId($productId)
    {
        $sourceCollection = $this->sourceCollectionFactory->create();
        $sources = $sourceCollection->getItems();
        foreach ($sources as $source) {
            if ($source->getId() != 'default' && $source->getEnabled()) {
                return $source;
            }
        }

        return false;
    }

    public function getVendorIdByLocationId($locationId)
    {
        /** @var \Omnyfy\Vendor\Model\Resource\Location\Collection $collection */
        $collection = $this->locationCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['eq' => $locationId]);

        if ($collection->count() == 1)
            return $collection->getFirstItem()->getData('vendor_id');

        return null;
    }
}
