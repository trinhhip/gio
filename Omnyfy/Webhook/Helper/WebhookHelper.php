<?php
namespace Omnyfy\Webhook\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Omnyfy\Webhook\Model\Config\Source\AuthenticationType;
use Omnyfy\Webhook\Model\WebhookRepository;
use Omnyfy\Webhook\Model\WebhookEventResponseFactory;
use Omnyfy\Webhook\Model\WebhookEventHistoryFactory;
use Omnyfy\Webhook\Model\WebhookEventHistory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use \Psr\Log\LoggerInterface;

class WebhookHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $curlFactory;

    protected $dataHelper;

    protected $webhookRepository;

    protected $webhookEventResponseFactory;

    protected $webhookEventHistoryFactory;

    protected $jsonHelper;

    protected $logger;

    public function __construct(
        Context $context,
        CurlFactory $curlFactory,
        Data $dataHelper,
        WebhookRepository $webhookRepository,
        WebhookEventResponseFactory $webhookEventResponseFactory,
        WebhookEventHistoryFactory $webhookEventHistoryFactory,
        JsonHelper $jsonHelper,
        LoggerInterface $logger
    ) {
        $this->curlFactory = $curlFactory;
        $this->dataHelper = $dataHelper;
        $this->webhookRepository = $webhookRepository;
        $this->webhookEventResponseFactory = $webhookEventResponseFactory;
        $this->webhookEventHistoryFactory = $webhookEventHistoryFactory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @param $eventItem
     * @param $webhookId
     * @return \Omnyfy\Webhook\Model\WebhookEventResponse|null
     */
    public function send($eventItem, $webhookId)
    {

        try {
            $eventItemArr = $this->jsonHelper->jsonDecode($eventItem);
            $webhook = $this->webhookRepository->getById($webhookId);
            $headersConfig = [];

            $headersConfig[] = 'Authorization: ' . $this->getAuthHeader();

            $headersConfig[] = 'Content-Type: ' . $webhook->getContentType();

            $curl = $this->curlFactory->create();
            $curl->write('POST', $webhook->getEndpointUrl(), '1.1', $headersConfig, $this->jsonHelper->jsonEncode($eventItemArr));

            $response = $curl->read();

            $webhookHistoryId = $this->addWebhookEventHistory($eventItem, $response, $webhookId);
            $webhookEventResponse = $this->webhookEventResponseFactory->create();
            $webhookEventResponse->addData([
                'history_id' => $webhookHistoryId,
                'body' =>\Zend_Http_Response::extractBody($response),
                'status_code' => \Zend_Http_Response::extractCode($response)
            ]);
            $webhookEventResponse->save();
        } catch (\Exception $e) {
            $this->logger->critical(__("Error when send webhook: %1", $e->getMessage()));
            return null;
        }

        return $webhookEventResponse;
    }

    /**
     * @param string $eventItem
     * @param string $response
     * @param int $webhookId
     * @return \Omnyfy\Webhook\Model\WebhookEventResponse
     * @throws \Exception
     */
    public function addWebhookEventHistory($eventItem, $response, $webhookId)
    {
        $eventItemArr = $this->jsonHelper->jsonDecode($eventItem);
        $responseStatus = \Zend_Http_Response::extractCode($response);
        $webhookHistory = $this->webhookEventHistoryFactory->create();
        $webhookStatus = $responseStatus == 200 ? WebhookEventHistory::STATUS_SUCCESS : WebhookEventHistory::STATUS_FAIL;
        $webhookHistory->setData(
            [
                'webhook_id' => $webhookId,
                'status' => $webhookStatus,
                'body' => $this->jsonHelper->jsonEncode($eventItemArr),
                'event_id' => $eventItemArr['event_id']
            ]
        );
        $webhookHistory->save();
        return $webhookHistory->getId();
    }

    /**
     * @return string|null
     */
    public function getAuthHeader()
    {
        if ($this->dataHelper->getConfig(Data::XML_PATH_AUTH_TYPE) == AuthenticationType::TYPE_BASIC) {
            $username = $this->dataHelper->getConfig(Data::XML_PATH_AUTH_USER_NAME);
            $password = $this->dataHelper->getConfig(Data::XML_PATH_AUTH_PASSWORD);
            if (!empty($username) && !empty($password)) {
                return 'Basic ' . base64_encode("{$username}:{$password}");
            }
        }

        if ($this->dataHelper->getConfig(Data::XML_PATH_AUTH_TYPE) == AuthenticationType::TYPE_BEARER) {
            if (!empty($this->dataHelper->getConfig(Data::XML_PATH_AUTH_TOKEN))) {
                return 'Bearer ' . $this->dataHelper->getConfig(Data::XML_PATH_AUTH_TOKEN);
            }
        }
        return null;
    }
}
