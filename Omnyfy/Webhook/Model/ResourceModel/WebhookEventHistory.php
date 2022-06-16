<?php
namespace Omnyfy\Webhook\Model\ResourceModel;

class WebhookEventHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_webhook_event_history', 'entity_id');
    }
}