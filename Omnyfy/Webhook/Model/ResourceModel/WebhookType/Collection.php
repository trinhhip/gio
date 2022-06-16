<?php
namespace Omnyfy\Webhook\Model\ResourceModel\WebhookType;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Omnyfy\Webhook\Model\WebhookType',
            'Omnyfy\Webhook\Model\ResourceModel\WebhookType'
        );
    }
}