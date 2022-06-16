<?php
namespace Omnyfy\Webhook\Model\ResourceModel\WebhookEventResponse;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Omnyfy\Webhook\Model\WebhookEventResponse',
            'Omnyfy\Webhook\Model\ResourceModel\WebhookEventResponse'
        );
    }
}