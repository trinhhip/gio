<?php
namespace Omnyfy\Webhook\Model\ResourceModel;

class Webhook extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_webhook', 'entity_id');
    }
}