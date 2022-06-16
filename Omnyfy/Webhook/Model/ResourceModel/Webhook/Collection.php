<?php
namespace Omnyfy\Webhook\Model\ResourceModel\Webhook;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Omnyfy\Webhook\Model\Webhook',
            'Omnyfy\Webhook\Model\ResourceModel\Webhook'
        );
    }
}