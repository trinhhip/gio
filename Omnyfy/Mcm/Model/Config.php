<?php
/**
 * Project: MCM
 * User: jing
 * Date: 23/1/20
 * Time: 3:09 pm
 */
namespace Omnyfy\Mcm\Model;

class Config
{
    const XML_PATH_MCM_ENABLE                       = 'omnyfy_mcm/general/fees_management';
    const XML_PATH_INCLUDE_KYC                      = 'omnyfy_mcm/general/include_kyc';
    const XML_PATH_TRANS_FEE_TAX_RATE               = 'omnyfy_mcm/transaction_fees/transaction_fee_tax_rate';
    const XML_PATH_CATEGORY_COMMISSIONS_ENABLE      = 'omnyfy_mcm/category_commissions/enable';
    const XML_PATH_ALLOW_TRANS_FEE                  = 'omnyfy_mcm/transaction_fees/allow_transaction_fees';
    const XML_PATH_TRANS_FEE_PERCENTAGE             = 'omnyfy_mcm/transaction_fees/transaction_fee_percentage';
    const XML_PATH_TRANS_FEE_AMOUNT                 = 'omnyfy_mcm/transaction_fees/transaction_fee_amount';
    const XML_PATH_TRANS_FEE_SURCHARGE_PERCENTAGE   = 'omnyfy_mcm/transaction_fees/transaction_fee_surcharge_percentage';
    const XML_PATH_ALLOW_VENDOR_FEE                 = 'omnyfy_mcm/set_default_fees/allow_vendor_fees';
    const XML_PATH_DEFAULT_SELLER_FEE               = 'omnyfy_mcm/set_default_fees/default_seller_fees';
    const XML_PATH_DEFAULT_MIN_SELLER_FEE           = 'omnyfy_mcm/set_default_fees/default_min_seller_fees';
    const XML_PATH_DEFAULT_MAX_SELLER_FEE           = 'omnyfy_mcm/set_default_fees/default_max_seller_fees';
    const XML_PATH_DEFAULT_DISBURSEMENT_FEE         = 'omnyfy_mcm/set_default_fees/default_disbursment_fees';
    const XML_PATH_ALLOW_REFUND_COMMERCIAL          = 'omnyfy_mcm/refund/allow_refund_commercials_management';
    const XML_PATH_REFUND_CATEGORY_COMMISSION       = 'omnyfy_mcm/refund/refund_category_management';
    const XML_PATH_REFUND_SELLER_FEE                = 'omnyfy_mcm/refund/refund_seller_fee';
    const XML_PATH_REFUND_DISBURSEMENT_FEE          = 'omnyfy_mcm/refund/refund_disbursment_fee';
    const XML_PATH_CHARGE_TRANS_FEE_FOR_REFUND      = 'omnyfy_mcm/refund/charge_transaction_fee_for_refund';
    const XML_PATH_MCM_ENABLE_WHOLESALE             = 'omnyfy_mcm/general/enable_wholesale';
    const XML_PATH_MCM_SELECT_WHOLESALE             = 'omnyfy_mcm/general/select_wholesale';
    const XML_PATH_MCM_QUESTION_WHOLESALE           = 'omnyfy_mcm/general/question';
    const XML_PATH_MCM_DEFAULT_VENDOR_PAYOUT        = 'omnyfy_mcm/general/default_vendor_payout';
    const XML_PATH_STORE_CITY                       = 'general/store_information/city';
    const XML_PATH_STORE_REGION                     = 'general/store_information/region_id';
    const XML_PATH_STORE_POSTCODE                   = 'general/store_information/postcode';
    const XML_PATH_STORE_COUNTRY                    = 'general/store_information/country_id';
    const XML_PATH_STORE_PHONE                      = 'general/store_information/phone';
    const XML_PATH_STORE_STREET_LINE1               = 'general/store_information/street_line1';
    const XML_PATH_STORE_STREET_LINE2               = 'general/store_information/street_line2';

    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    public function isIncludeKyc()
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_INCLUDE_KYC);
    }

    public function getEnableWholeSale()
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_MCM_ENABLE_WHOLESALE);
    }

    public function getSelectWholeSale()
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_MCM_SELECT_WHOLESALE);
    }

    public function getWholeSaleQuestion()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_MCM_QUESTION_WHOLESALE);
    }

    public function getDefaultVendorPayout()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_MCM_DEFAULT_VENDOR_PAYOUT);
    }

    public function getStoreCity()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_STORE_CITY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getStoreRegion()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_STORE_REGION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getStorePostCode()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_STORE_POSTCODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getStoreCountry()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_STORE_COUNTRY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getStorePhone()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_STORE_PHONE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getStoreStreetLine1()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_STORE_STREET_LINE1, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getStoreStreetLine2()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_STORE_STREET_LINE2, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
 
