<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Model\Rules\Config;


class CountryOptions extends \Magento\Directory\Model\Config\Source\Country implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray($isMultiselect = false, $foregroundCountries = ''){
        return parent::toOptionArray(true);
    }

}