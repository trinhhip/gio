<?php
namespace Omnyfy\Webhook\Model\ResourceModel\WebhookEventSchedule;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Omnyfy\Webhook\Model\WebhookEventSchedule',
            'Omnyfy\Webhook\Model\ResourceModel\WebhookEventSchedule'
        );
    }
}