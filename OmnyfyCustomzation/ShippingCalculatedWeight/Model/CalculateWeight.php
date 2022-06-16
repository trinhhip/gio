<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Model;


use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class CalculateWeight extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'calculate_weight';

    protected $_cacheTag = 'calculate_weight';

    protected $_eventPrefix = 'calculate_weight';

    protected function _construct()
    {
        $this->_init(\OmnyfyCustomzation\ShippingCalculatedWeight\Model\ResourceModel\CalculateWeight::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        return [];
    }
}