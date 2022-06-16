<?php
namespace Omnyfy\Webhook\Cron;
use Omnyfy\Webhook\Model\ResourceModel\WebhookEventSchedule\CollectionFactory as WebhookEventScheduleCollection;
use Omnyfy\Webhook\Helper\WebhookHelper;
use Omnyfy\Webhook\Helper\Data;
use Omnyfy\Webhook\Model\WebhookEventScheduleRepository;
use Omnyfy\Webhook\Model\WebhookEventSchedule as WebhookScheduleModel;

class WebhookEventSchedule
{
    protected $webhookEventScheduleCollection;

    protected $webhookHelper;

    protected $webhookEventScheduleRepository;

    protected $dataHelper;

    public function __construct(
        WebhookEventScheduleRepository $webhookEventScheduleRepository,
        WebhookEventScheduleCollection $webhookEventScheduleCollection,
        WebhookHelper $webhookHelper,
        Data $dataHelper
    ) {
        $this->webhookEventScheduleCollection = $webhookEventScheduleCollection;
        $this->webhookHelper = $webhookHelper;
        $this->webhookEventScheduleRepository = $webhookEventScheduleRepository;
        $this->dataHelper = $dataHelper;
    }

    public function execute()
    {
        $pendingWebhooks = $this->webhookEventScheduleRepository->getPendingWebhookEvents();
        /** @var \Omnyfy\Webhook\Model\WebhookEventSchedule $item */
        foreach ($pendingWebhooks->getItems() as $item) {
            if (!$this->dataHelper->isEnableSchedule($item->getStoreId())) {
                continue;
            }
            try {
                $id = $item->getId();
                $resource = $item->getResource();
                $resource->updateStatus($id, WebhookScheduleModel::STATUS_INPROGRESS);
                $webhookEventResponse = $this->webhookHelper->send($item->getBody(), $item->getWebhookId());
                if ($webhookEventResponse !== null) {
                    $resource->updateStatus($id, WebhookScheduleModel::STATUS_SUCCESS);
                } else {
                    $resource->updateStatus($id, WebhookScheduleModel::STATUS_FAILED);
                }
            } catch (\Exception $e) {
                $resource->updateStatus($id, WebhookScheduleModel::STATUS_FAILED);
            }
        }
    }
}
