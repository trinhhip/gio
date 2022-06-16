<?php


namespace OmnyfyCustomzation\SalesSequence\Helper;


use Magento\Framework\App\ResourceConnection;

class Data
{
    const PATTERN = "%s%'.03d";
    /**
     * @var ResourceConnection
     */
    protected $resource;

    public function __construct(
        ResourceConnection $resource
    )
    {
        $this->resource = $resource;
    }

    public function getIncrementId($shippingAddress, $sequence)
    {
        $countryCode = $shippingAddress->getCountryId();
        $year = date("y");
        $name = substr($shippingAddress->getFirstname(), 0, 3);
        $prefix = strtoupper($countryCode . $year . '-' . $name . '-');
        return sprintf(
            self::PATTERN,
            $prefix,
            $sequence
        );
    }

}