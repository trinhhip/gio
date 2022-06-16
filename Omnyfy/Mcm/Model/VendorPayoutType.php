<?php
namespace Omnyfy\Mcm\Model;
class VendorPayoutType extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'omnyfy_mcm_vendor_payout_type';

    protected $_cacheTag = 'omnyfy_mcm_vendor_payout_type';

    protected $_eventPrefix = 'omnyfy_mcm_vendor_payout_type';

    protected function _construct()
    {
        $this->_init(\Omnyfy\Mcm\Model\ResourceModel\VendorPayoutType::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}