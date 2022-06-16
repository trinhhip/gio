<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Helper;


use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\ListsInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_DIMENSION_MULTIPLIER = 'carriers/calculateweight/dimension_multiplier';
    const XML_PATH_WEIGHT_MULTIPLIER = 'carriers/calculateweight/weight_multiplier';
    /**
     * @var ListsInterface
     */
    public $localeLists;

    public function __construct(
        Context $context,
        ListsInterface $localeLists
    )
    {
        $this->localeLists = $localeLists;
        parent::__construct($context);
    }


    public function getCountryOptions()
    {
        return $this->localeLists->getOptionCountries();
    }

    public function getDimensionMultiplier()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DIMENSION_MULTIPLIER, ScopeInterface::SCOPE_STORE);
    }

    public function getWeightMultiplier()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WEIGHT_MULTIPLIER, ScopeInterface::SCOPE_STORE);
    }

    public function getCalculatedShippingWeight($product)
    {
        $weigh = (float)$product->getWeight();
        $length = (float)$product->getomnyfyDimensionsLength();
        $width = (float)$product->getOmnyfyDimensionsWidth();
        $height = (float)$product->getomnyfyDimensionsHeight();
        $dimensionMultiplier = (float)$this->getDimensionMultiplier();
        $weightMultiplier = (float)$this->getWeightMultiplier();

        $cswByDimension = (($length * $width * $height) / 5000) * $dimensionMultiplier;
        $cswByWeigh = $weigh * $weightMultiplier;
        $calculatedShippingWeight = ($cswByDimension > $cswByWeigh) ? $cswByDimension : $cswByWeigh;
        return round($calculatedShippingWeight, 2);
    }
}