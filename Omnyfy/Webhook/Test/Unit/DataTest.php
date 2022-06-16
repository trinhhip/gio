<?php

namespace Omnyfy\Webhook\Test\Unit;

use Omnyfy\Webhook\Helper\Data;
use Omnyfy\Webhook\Helper\WebhookHelper;
use \Psr\Log\LoggerInterface;
use Omnyfy\Webhook\Model\Config\Source\AuthenticationType;
use Magento\Store\Model\ScopeInterface;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    protected $context;

    protected function setUp()
    {
        $methods = array_merge(
            get_class_methods(\Magento\Framework\App\Config\ScopeConfigInterface::class)
        );

        $this->scopeConfigMock =  $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock->method('getValue')
            ->willReturnMap([
                [Data::XML_PATH_AUTH_TYPE, ScopeInterface::SCOPE_STORE, null, AuthenticationType::TYPE_BEARER],
                [Data::XML_PATH_AUTH_USER_NAME, ScopeInterface::SCOPE_STORE, null, 'user'],
                [Data::XML_PATH_AUTH_PASSWORD, ScopeInterface::SCOPE_STORE, null, 'password'],
                [Data::XML_PATH_AUTH_TOKEN, ScopeInterface::SCOPE_STORE, null, 'token']
            ]);

        $context = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->helper = $objectManager->getObject(
            \Omnyfy\Webhook\Helper\Data::class,
            ['context' => $context]
        );
    }

    /**
     * @covers \Omnyfy\Webhook\Helper\Data::getConfig($path)
     *
     * @param string $path
     * @param string $expectedResult
     *
     * @dataProvider getConfigProvider
     */
    public function testGetConfig(string $path, string $expectedResult)
    {
        $this->assertEquals(
            $this->helper->getConfig($path),
            $expectedResult
        );
    }

    /**
     * @return array[]
     */
    public function getConfigProvider()
    {
        return [
            [Data::XML_PATH_AUTH_TYPE, AuthenticationType::TYPE_BEARER],
            [Data::XML_PATH_AUTH_USER_NAME, 'user'],
            [Data::XML_PATH_AUTH_PASSWORD, 'password'],
            [Data::XML_PATH_AUTH_TOKEN, 'token']
        ];
    }
}
