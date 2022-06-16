<?php
namespace Omnyfy\Webhook\Model\ResourceModel;

class WebhookEventResponse extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_webhook_event_response', 'entity_id');
    }
}