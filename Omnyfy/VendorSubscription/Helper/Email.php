<?php
/**
 * Project: Vendor Subscription
 * User: jing
 * Date: 2/10/19
 * Time: 5:31 pm
 */
namespace Omnyfy\VendorSubscription\Helper;

use Omnyfy\VendorSubscription\Model\Config;
use Magento\Framework\App\Area;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $emailHelper;

    protected $priceHelper;

    protected $_storeManager;

    protected $intervalSource;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Omnyfy\Core\Helper\Email $emailHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Omnyfy\VendorSubscription\Model\Source\Interval $intervalSource
    ) {
        $this->_storeManager = $storeManager;
        $this->emailHelper = $emailHelper;
        $this->priceHelper = $priceHelper;
        $this->intervalSource = $intervalSource;
        parent::__construct($context);
    }


    private function sendEmail($subscription, $templateId, $sendToAdmin = false){
        $store = $this->_storeManager->getStore();
        $vars = [
            'vendor_name' => $subscription->getVendorName(),
            'end_date' => $this->parseDate($subscription->getExpiryAt()),
            'plan_name' => $subscription->getPlanName(),
            'plan_price' => $this->parsePrice($subscription->getPlanPrice()),
            'plan_description' => $subscription->getDescription(),
            'invoice_link' => $subscription->getData('invoice_link'),
            'billing_interval' => $this->parseInterval($subscription->getBillingInterval()),
            'store' => $store
        ];

        $from = 'general';
        $to = [
            'email' => $subscription->getVendorEmail(),
            'name' => $subscription->getVendorName()
        ];
        if($sendToAdmin) {
            $to = [
                'email' => $this->scopeConfig->getValue(Config::XML_PATH_ADMIN_EMAIL),
                'name' => $this->scopeConfig->getValue(Config::XML_PATH_ADMIN_NAME)
            ];
        }
        $storeId = $store->getId();

        $this->emailHelper->sendEmail($templateId, $vars, $from, $to,Area::AREA_FRONTEND, $storeId);
    }

    public function sendInvoiceFailed($subscription){
        $templateVendorId = $this->scopeConfig->getValue(Config::INVOICE_FAILED_VENDOR_TEMPLATE) ?: 'omnyfy_subscription_general_invoice_failed_vendor';
        $this->sendEmail($subscription, $templateVendorId);
        $templateAdminId = $this->scopeConfig->getValue(Config::INVOICE_FAILED_ADMIN_TEMPLATE) ?: 'omnyfy_subscription_general_invoice_failed_mo';
        $this->sendEmail($subscription,$templateAdminId, true);
    }


    public function sendSubscriptionExpiry($subscription){
        $templateVendorId = $this->scopeConfig->getValue(Config::SUBSCRIPTION_EXPIRY_VENDOR_TEMPLATE) ?: 'omnyfy_subscription_general_subscription_expiry_vendor';
        $this->sendEmail($subscription, $templateVendorId);
        $templateAdminId = $this->scopeConfig->getValue(Config::SUBSCRIPTION_EXPIRY_ADMIN_TEMPLATE) ?: 'omnyfy_subscription_general_subscription_expiry_mo';
        $this->sendEmail($subscription,$templateAdminId, true);
    }

    public function sendCancelEmails($subscription)
    {
        $templateVendorId = $this->scopeConfig->getValue(Config::CANCEL_SUBSCRIPTION_VENDOR_TEMPLATE) ?: 'omnyfy_subscription_general_cancel_template_vendor';
        $this->sendEmail($subscription, $templateVendorId);
        $templateAdminId = $this->scopeConfig->getValue(Config::CANCEL_SUBSCRIPTION_ADMIN_TEMPLATE) ?: 'omnyfy_subscription_general_cancel_template_admin';
        $this->sendEmail($subscription,$templateAdminId, true);
    }

    public function sendInvoiceEmails($subscription)
    {
        $templateVendorId = $this->scopeConfig->getValue(Config::INVOICE_VENDOR_TEMPLATE) ?: 'omnyfy_subscription_general_invoice_template_vendor';
        $this->sendEmail($subscription, $templateVendorId);
        $templateAdminId = $this->scopeConfig->getValue(Config::INVOICE_ADMIN_TEMPLATE) ?: 'omnyfy_subscription_general_invoice_template_admin';
        $this->sendEmail($subscription,$templateAdminId, true);
    }

    public function sendUpdateSubscription($subscription)
    {
        $templateVendorId = $this->scopeConfig->getValue(Config::UPDATE_SUBSCRIPTION_VENDOR_TEMPLATE) ?: 'omnyfy_subscription_general_subscription_update_template_vendor';
        $this->sendEmail($subscription, $templateVendorId);
        $templateAdminId = $this->scopeConfig->getValue(Config::UPDATE_SUBSCRIPTION_ADMIN_TEMPLATE) ?: 'omnyfy_subscription_general_subscription_update_template_admin';
        $this->sendEmail($subscription,$templateAdminId, true);
    }

    public function parseInterval($interval)
    {
        $map = $this->intervalSource->toValuesArray();
        if (array_key_exists($interval, $map)) {
            return $map[$interval];
        }

        return '';
    }

    public function parseDate($date)
    {
        return date('d/M/Y', strtotime($date));
    }

    public function parsePrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }
}
