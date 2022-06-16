<?php


namespace OmnyfyCustomzation\B2C\Plugin\Checkout;


use OmnyfyCustomzation\B2C\Helper\Data;

class CheckoutConfig
{
    /**
     * @var Data
     */
    public $helperData;

    public function __construct(
        Data $helperData
    )
    {
        $this->helperData = $helperData;
    }

    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, $result)
    {
        $result['b2c_allows_countries'] = $this->helperData->getAllowCountries();
        return $result;
    }
}
