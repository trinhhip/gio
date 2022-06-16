<?php
namespace Omnyfy\Webhook\Model\ResourceModel;

class WebhookEventSchedule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_webhook_event_schedule', 'entity_id');
    }

    public function updateStatus($id, $status)
    {
        $conn = $this->getConnection();
        $conn->update(
            $this->getMainTable(),
            ['status' => $status],
            'entity_id = ' . $id
        );
    }
}
