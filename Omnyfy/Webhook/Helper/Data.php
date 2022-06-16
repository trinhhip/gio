<?php
namespace Omnyfy\Webhook\Helper;

use Magento\Store\Model\ScopeInterface;
use Omnyfy\Webhook\Model\Webhook;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_IS_ENABLE = 'webhook/general/is_enable';

    const XML_PATH_IS_ENABLE_SCHEDULE = 'webhook/general/is_enable_schedule';

    const XML_PATH_AUTH_USER_NAME = 'webhook/authentication/username';

    const XML_PATH_AUTH_PASSWORD = 'webhook/authentication/password';

    const XML_PATH_AUTH_TOKEN = 'webhook/authentication/token';

    const XML_PATH_AUTH_TYPE = 'webhook/authentication/type';

    const XML_PATH_EVENT_HISTORY_ROTATION = 'webhook/general/event_history_rotation';

    protected $webhookCollectionFactory;
    protected $typeCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Omnyfy\Webhook\Model\ResourceModel\Webhook\CollectionFactory $webhookCollectionFactory,
        \Omnyfy\Webhook\Model\ResourceModel\WebhookType\CollectionFactory $typeCollectionFactory
    ){
        parent::__construct($context);
        $this->typeCollectionFactory = $typeCollectionFactory;
        $this->webhookCollectionFactory = $webhookCollectionFactory;
    }

    public function isEnable($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_IS_ENABLE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isEnableSchedule($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_IS_ENABLE_SCHEDULE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getWebhookByTypeId($webhookTypeId, $storeId = 0)
    {
        $collection = $this->webhookCollectionFactory->create();
        $collection->addFieldToFilter('status', Webhook::STATUS_ENABLED);
        $collection->addFieldToFilter('webhook_type_id', $webhookTypeId);
        $collection->addFieldToFilter('store_id', $storeId);
        return $collection;
    }

    public function getWebhookTypeIdByType($type)
    {
        $collection = $this->typeCollectionFactory->create()
            ->addFieldToFilter('type', $type);

        if (count($collection) > 0) {
            return $collection->getFirstItem();
        }else{
            return null;
        }
    }
}
