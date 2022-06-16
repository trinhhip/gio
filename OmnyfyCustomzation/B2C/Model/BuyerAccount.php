<?php


namespace OmnyfyCustomzation\B2C\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class BuyerAccount extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'buyer_account';

    protected $_cacheTag = 'buyer_account';

    protected $_eventPrefix = 'buyer_account';

    protected function _construct()
    {
        $this->_init(ResourceModel\BuyerAccount::class);
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
