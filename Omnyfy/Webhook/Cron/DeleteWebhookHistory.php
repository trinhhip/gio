<?php
namespace Omnyfy\Webhook\Cron;

use Omnyfy\Webhook\Model\ResourceModel\WebhookEventHistory\CollectionFactory;
use Omnyfy\Webhook\Helper\Data;

class DeleteWebhookHistory
{
    protected $collectionFactory;

    protected $dataHelper;

    public function __construct(
        CollectionFactory $collectionFactory,
        Data $dataHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
    }

    public function execute()
    {
        $today = date('Y-m-d');
        $daysRotation = $this->dataHelper->getConfig(Data::XML_PATH_EVENT_HISTORY_ROTATION);
        $dateBefore = date('Y-m-d', strtotime("-$daysRotation days", strtotime($today)));
        $historyToDelete = $this->collectionFactory->create()
            ->addFieldToFilter('created_at', ['lt' => $dateBefore]);
        foreach ($historyToDelete->getItems() as $item) {
            $item->delete();
        }
    }
}
