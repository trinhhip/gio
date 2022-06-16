<?php
/**
 * Project: Multi Vendor.
 * User: jing
 * Date: 21/8/18
 * Time: 2:44 PM
 */
namespace Omnyfy\Vendor\Model;

use Magento\Store\Model\ScopeInterface;

class Config
{

    const XML_PATH_VENDOR_SHARE_PRODS = 'omnyfy_vendor/vendor/is_sku_sharing';

    const XML_PATH_ADMIN_SEE_ALL_SKU = 'omnyfy_vendor/vendor/is_admin_see_all_sku';

    const XML_PATH_BIND_INCLUDE_WAREHOUSE = 'omnyfy_vendor/vendor/bind_include_warehouse';

    const XML_PATH_INVOICE_BY = 'omnyfy_vendor/vendor/invoice_by';

    const XML_PATH_SHIPMENT_BY = 'omnyfy_vendor/vendor/shipment_by';

    const XML_PATH_READONLY_MO_PRODUCTS = 'omnyfy_vendor/vendor/readonly_mo_products';

    const XML_PATH_VENDOR_TYPE_IDS = 'omnyfy_vendor/vendor/vendor_type_ids';

    const XML_PATH_MO_ABN = 'omnyfy_vendor/vendor/mo_abn';

    const XML_PATH_MO_NAME = 'general/store_information/name';

    const XML_PATH_ORDER_WEBHOOK_ENDPOINT = 'omnyfy_vendor/vendor/order_webhook_endpoint';

    const XML_PATH_ORDER_COMPLETE_WEBHOOK_ENDPOINT = 'omnyfy_vendor/vendor/order_complete_webhook_endpoint';

    const XML_PATH_SUPPORT_LINK = 'omnyfy_vendor/vendor/support_link';

    const INVOICE_BY_MO = 1;

    const INVOICE_BY_VENDOR = 2;

    const XML_PATH_EMAIL_SENT_FROM ='omnyfy_vendor/order_notification/sent_from';

    const XML_PATH_ORDER_NOTIFICATION_TEMPLATE = 'omnyfy_vendor/order_notification/template';

    const XML_PATH_ORDER_NOTIFICATION_ORDER_STATUS = 'omnyfy_vendor/order_notification/order_status';

    const XML_PATH_ADMIN_EMAIL = 'trans_email/ident_support/email';

    const XML_PATH_ADMIN_NAME = 'trans_email/ident_support/name';

    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    public function isVendorShareProducts()
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_VENDOR_SHARE_PRODS);
    }

    public function getInvoiceBy()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_INVOICE_BY);
    }

    public function isReadonlyMoProducts()
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_READONLY_MO_PRODUCTS);
    }

    public function getCanBindVendorTypeIds()
    {
        $str = $this->_scopeConfig->getValue(self::XML_PATH_VENDOR_TYPE_IDS);
        $result = explode(',', $str);
        return empty($result) ? [] : $result;
    }

    public function getMoAbn()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_MO_ABN);
    }

    public function getMoName()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_MO_NAME);
    }

    public function isAdminSeeAllSku()
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_ADMIN_SEE_ALL_SKU);
    }

    public function isBindIncludeWarehouse()
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_BIND_INCLUDE_WAREHOUSE);
    }


    public function getOrderWebhookEndpoint()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_ORDER_WEBHOOK_ENDPOINT);
    }

    public function getCompleteOrderWebhookEndpoint()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_ORDER_COMPLETE_WEBHOOK_ENDPOINT);
    }

    public function getSupportLink()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_SUPPORT_LINK);
    }

    public function getEmailSentFrom($scopeCode = null){
       return $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_SENT_FROM,ScopeInterface::SCOPE_STORE,$scopeCode = null);
    }

    public function getOrderNotificationTemplate($scopeCode = null){
       return $this->_scopeConfig->getValue(self::XML_PATH_ORDER_NOTIFICATION_TEMPLATE,ScopeInterface::SCOPE_STORE,$scopeCode = null);
    }

    public function getOrderNotificationOrderStatus($scopeCode = null){
       return $this->_scopeConfig->getValue(self::XML_PATH_ORDER_NOTIFICATION_ORDER_STATUS,ScopeInterface::SCOPE_STORE,$scopeCode = null);
    }
}
