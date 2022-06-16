<?php
namespace Omnyfy\Webhook\Test\Unit;

use Omnyfy\Webhook\Helper\Data;
use Omnyfy\Webhook\Helper\WebhookHelper;
use \Psr\Log\LoggerInterface;
use Omnyfy\Webhook\Model\Config\Source\AuthenticationType;

class WebhookHelperTest extends \PHPUnit\Framework\TestCase
{
    protected $helper;

    protected $context;

    protected $scopeConfig;

    protected $curlFactory;

    protected $curl;

    protected $dataHelper;

    protected $webhookFactory;

    protected $webhook;

    protected $webhookCollectionFactory;

    protected $webhookCollection;

    protected $searchResultInterfaceFactory;

    protected $searchResultInterface;

    protected $webhookResponseFactory;

    protected $webhookResponse;

    protected $webhookHistoryFactory;

    protected $webhookHistory;

    protected $logger;

    protected $jsonHelper;

    protected $webhookRepository;

    protected $eventItem = '{"event_id":"id","event_type":"type","data":{"key":"value"}}';

    protected $eventItemArr = [
        'event_id' => 'id',
        'event_type' => 'type',
        'data' => [
            'key' => 'value'
        ]
    ];

    protected $body = '{"key" : "value"}';

    protected $webhookId = 1;

    protected $response = "HTTP/1.1 200 OK\nServer: nginx/1.14.2\nContent-Type: text/plain; charset=UTF-8\nVary: Accept-Encoding\nX-Request-Id: 89bab6d5-7b96-4684-93e9-f355f0735f0e\nX-Token-Id: 2d6a7555-99ae-4b24-958c-e94541124953\nCache-Control: no-cache, private\nDate: Fri, 26 Feb 2021 09:22:47 GMT\nSet-Cookie: laravel_session=aFSuLmsxfxSbD8rRFXZst7nAjphzAODerHKSu4R9; expires=Fri, 26-Feb-2021 11:22:47 GMT; Max-Age=7200; path=/; httponly\r\n\r\nThis is response body";

    public function setUp()
    {
        $this->mockContext();
        $this->dataHelper = $this->createMock(Data::class);
        $this->mockWebhook();
        $this->mockWebhookCollection();
        $this->mockSearchResultInterface();
        $this->curlFactory = $this->getMockBuilder(\Magento\Framework\HTTP\Adapter\CurlFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->webhookRepository = $this->createMock(\Omnyfy\Webhook\Model\WebhookRepository::class);
        $this->mockWebhookHistory();
        $this->jsonHelper = $this->createMock(\Magento\Framework\Json\Helper\Data::class);
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->webhookResponseFactory = $this->getMockBuilder(\Omnyfy\Webhook\Model\WebhookEventResponseFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->helper = $objectManager->getObject(
            \Omnyfy\Webhook\Helper\WebhookHelper::class,
            [
                'context' => $this->context,
                'curlFactory' => $this->curlFactory,
                'dataHelper' => $this->dataHelper,
                'webhookRepository' => $this->webhookRepository,
                'webhookEventResponseFactory' => $this->webhookResponseFactory,
                'webhookEventHistoryFactory' => $this->webhookHistoryFactory,
                'jsonHelper' => $this->jsonHelper,
                'logger' => $this->logger
            ]
        );
    }

    /**
     * @covers \Omnyfy\Webhook\Helper\WebhookHelper::send
     */
    public function testSend()
    {
        $expectedWebhookHistoryId = 1;
        $helperMock = $this->getMockBuilder(\Omnyfy\Webhook\Helper\WebhookHelper::class)->disableOriginalConstructor()->getMock();

        $this->jsonHelper->expects($this->any())->method('jsonDecode')->with($this->eventItem)->willReturn($this->eventItemArr);
        $webhook = $this->createMock(\Omnyfy\Webhook\Model\Webhook::class);
        $this->webhookRepository->expects($this->once())->method('getById')->with($this->webhookId)->willReturn($webhook);

        /**
         * @cover WebhookHelper::getAuthHeader
         */
        $this->dataHelper->method('getConfig')
            ->willReturnMap([
                [Data::XML_PATH_AUTH_TYPE, AuthenticationType::TYPE_BEARER],
                [Data::XML_PATH_AUTH_TOKEN, 'token']
            ]);
        $helperMock->expects($this->any())->method('getAuthHeader')
            ->willReturn('Bearer token');

        $expectedHeadersConfig = [
            'Authorization: Bearer token',
            'Content-Type: application/json'
        ];

        $webhook->expects($this->once())->method('getContentType')->willReturn('application/json');
        $webhook->expects($this->once())->method('getEndpointUrl')->willReturn('endpoint_url');

        $this->curl = $this->getMockBuilder(\Magento\Framework\HTTP\Adapter\Curl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->curlFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->curl);
        $this->jsonHelper->expects($this->any())->method('jsonEncode')->with($this->eventItemArr['data'])->willReturn($this->body);
        $this->curl->expects($this->once())->method('write')
            ->with(
                'POST',
                'endpoint_url',
                '1.1',
                $expectedHeadersConfig,
                $this->body
            );
        $this->curl->expects($this->once())->method('read')->willReturn($this->response);

        /**
         * @cover WebhookHelper::addWebhookEventHistory
         */
        $this->webhookHistoryFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->webhookHistory);
        $this->webhookHistory->expects($this->any())->method('setData')->with(
            [
                'webhook_id' => $this->webhookId,
                'status' => 1,
                'body' => $this->body,
                'event_id' => $this->eventItemArr['event_id']
            ]
        )->willReturnSelf();
        $this->webhookHistory->expects($this->any())->method('save')->willReturnSelf();
        $this->webhookHistory->expects($this->any())->method('getId')->willReturn($expectedWebhookHistoryId);
        $helperMock->expects($this->any())->method('addWebhookEventHistory')
            ->with($this->eventItem, $this->response, $this->webhookId)
            ->willReturn($expectedWebhookHistoryId);

        $this->webhookResponse = $this->getMockBuilder(\Omnyfy\Webhook\Model\WebhookEventResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->webhookResponseFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->webhookResponse);

        $this->webhookResponse->expects($this->once())->method('addData')->with([
            'history_id' => $expectedWebhookHistoryId,
            'body' => 'This is response body',
            'status_code' => 200
        ]);
        $this->webhookResponse->expects($this->once())->method('save');

        $this->assertEquals($this->webhookResponse, $this->helper->send($this->eventItem, $this->webhookId));
    }

    /**
     * @covers \Omnyfy\Webhook\Helper\WebhookHelper::addWebhookEventHistory
     */
    public function testAddWebhookEventHistory()
    {
        $this->jsonHelper->expects($this->any())->method('jsonDecode')->with($this->eventItem)->willReturn($this->eventItemArr);
        $this->jsonHelper->expects($this->any())->method('jsonEncode')->with($this->eventItemArr['data'])->willReturn($this->body);
        $this->webhookHistoryFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->webhookHistory);
        $this->webhookHistory->expects($this->any())->method('setData')->with(
            [
                'webhook_id' => $this->webhookId,
                'status' => 1,
                'body' => $this->body,
                'event_id' => $this->eventItemArr['event_id']
            ]
        )->willReturnSelf();
        $this->webhookHistory->expects($this->any())->method('save')->willReturnSelf();
        $this->webhookHistory->expects($this->any())->method('getId')->willReturn(1);
        $this->assertEquals(1, $this->helper->addWebhookEventHistory($this->eventItem, $this->response, $this->webhookId));
    }

    /**
     * @covers \Omnyfy\Webhook\Helper\WebhookHelper::getAuthHeader
     *
     * @param string $type
     * @param string $username
     * @param string $password
     * @param string $token
     * @param string $expectedResult
     * @dataProvider getAuthHeaderDataProvider
     */
    public function testGetAuthHeader($type, $username, $password, $token, $expectedResult) {
        $this->dataHelper->method('getConfig')
            ->willReturnMap([
                [Data::XML_PATH_AUTH_TYPE, $type],
                [Data::XML_PATH_AUTH_USER_NAME, $username],
                [Data::XML_PATH_AUTH_PASSWORD, $password],
                [Data::XML_PATH_AUTH_TOKEN, $token]
            ]);
        $this->assertEquals($expectedResult, $this->helper->getAuthHeader());
    }

    /**
     * @return array
     */
    public function getAuthHeaderDataProvider()
    {
        $user = 'user';
        $password = 'password';
        $token = 'token';
        return [
            AuthenticationType::TYPE_BEARER => [
                'type' => AuthenticationType::TYPE_BEARER,
                'username' => $user,
                'password' => $password,
                'token' => $token,
                'expectedResult' => 'Bearer token',
            ],
            AuthenticationType::TYPE_BASIC => [
                'type' => AuthenticationType::TYPE_BASIC,
                'username' => $user,
                'password' => $password,
                'token' => $token,
                'expectedResult' => 'Basic ' . base64_encode("{$user}:{$password}")
            ]
        ];
    }

    protected function mockContext()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfig);
    }

    protected function mockWebhook()
    {
        $this->webhookFactory = $this->getMockBuilder(\Omnyfy\Webhook\Model\WebhookFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->webhook = $this->createMock(\Omnyfy\Webhook\Model\Webhook::class);
        $this->webhookFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->webhook);
    }

    protected function mockWebhookCollection()
    {
        $this->webhookCollectionFactory = $this->getMockBuilder(\Omnyfy\Webhook\Model\ResourceModel\Webhook\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->webhookCollection = $this->getMockBuilder(\Omnyfy\Webhook\Model\ResourceModel\Webhook\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getSize', 'setCurPage', 'setPageSize', 'load', 'addOrder'])
            ->getMock();
        $this->webhookCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->webhookCollection);
    }

    protected function mockSearchResultInterface()
    {
        $this->searchResultInterfaceFactory = $this->getMockBuilder(\Omnyfy\Webhook\Api\Data\WebhookSearchResultsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchResultInterface = $this->getMockBuilder(\Omnyfy\Webhook\Api\Data\WebhookSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems', 'setItems', 'getSearchCriteria', 'setSearchCriteria', 'getTotalCount', 'setTotalCount'])
            ->getMock();
        $this->searchResultInterfaceFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->searchResultInterface);
    }

    protected function mockWebhookHistory()
    {
        $this->webhookHistoryFactory = $this->getMockBuilder(\Omnyfy\Webhook\Model\WebhookEventHistoryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $methods = array_merge(
            get_class_methods(\Omnyfy\Webhook\Model\WebhookEventHistory::class)
        );
        $this->webhookHistory = $this->getMockBuilder(\Omnyfy\Webhook\Model\WebhookEventHistory::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
