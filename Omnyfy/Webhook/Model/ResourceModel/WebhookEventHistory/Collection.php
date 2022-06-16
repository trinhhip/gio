<?php
namespace Omnyfy\Webhook\Model\ResourceModel\WebhookEventHistory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Omnyfy\Webhook\Model\WebhookEventHistory',
            'Omnyfy\Webhook\Model\ResourceModel\WebhookEventHistory'
        );
    }
}