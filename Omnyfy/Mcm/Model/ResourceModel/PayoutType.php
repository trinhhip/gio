<?php
namespace Omnyfy\Mcm\Model\ResourceModel;


class PayoutType extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('omnyfy_mcm_payout_type', 'id');
    }
}
