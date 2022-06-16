<?php
namespace Omnyfy\Webhook\Model\ResourceModel;

class WebhookType extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_webhook_type', 'entity_id');
    }
}