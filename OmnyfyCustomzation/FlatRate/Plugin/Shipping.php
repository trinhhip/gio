<?php


namespace OmnyfyCustomzation\FlatRate\Plugin;


use Magento\Framework\App\Config\ScopeConfigInterface;

class Shipping
{
    const FLAT_RATE_SHIP_FROM_COUNTRY = 'carriers/flatrate/ship_from_country';
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function aroundCollectCarrierRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Closure $proceed,
        $carrierCode,
        $request
    )
    {
        if ($carrierCode == 'flatrate') {
            $allowShipFromCountries = explode(',', $this->getShipFromCountries());
            foreach ($request->getAllItems() as $item){
                $productShipFromCountry = $item->getProduct()->getShipFromCountry();
                if (!in_array($productShipFromCountry, $allowShipFromCountries)){
                    return $subject;
                }
            }
        }
        return $proceed($carrierCode, $request);
    }

    public function getShipFromCountries()
    {
        return $this->scopeConfig->getValue(self::FLAT_RATE_SHIP_FROM_COUNTRY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
