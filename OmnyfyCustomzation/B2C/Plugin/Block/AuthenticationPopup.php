<?php


namespace OmnyfyCustomzation\B2C\Plugin\Block;


use OmnyfyCustomzation\B2C\Helper\Data;
use OmnyfyCustomzation\Customer\Block\Widget\CountryCode;

class AuthenticationPopup
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

    public function afterGetConfig(\Magento\Customer\Block\Account\AuthenticationPopup $subject, $result)
    {
        $countryCodeWidget = $subject->getLayout()->createBlock(CountryCode::class);
        $countryOptions = [];
        foreach ($countryCodeWidget->getOptions() as $key => $option) {
            if ($option->getValue() == '') {
                $countryOptions[$key]['value'] = $option->getValue();
                $countryOptions[$key]['label'] = __('Country Code');
            } else {
                $countryOptions[$key]['value'] = $option->getValue();
                $countryOptions[$key]['label'] = $option->getLabel();
            }
        }
        $b2cConfig = [
            'retailCreateSuccessMessage' => $this->helperData->getRetailCreateSuccessMessage(),
            'requestToTradeUrl' => $subject->getUrl('buyer/trade'),
            'countryOptions' => $countryOptions
        ];
        return array_merge($b2cConfig, $result);
    }
}
