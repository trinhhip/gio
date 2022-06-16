<?php
namespace Omnyfy\Mcm\Model;
class PayoutType extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const DEFAULT_TYPE = "Manual";

    const STRIPE_TYPE = "Stripe";

    const CACHE_TAG = 'omnyfy_mcm_payout_type';

    protected $_cacheTag = 'omnyfy_mcm_payout_type';

    protected $_eventPrefix = 'omnyfy_mcm_payout_type';

    protected function _construct()
    {
        $this->_init(\Omnyfy\Mcm\Model\ResourceModel\PayoutType::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
