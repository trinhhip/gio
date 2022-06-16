<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Test\Unit\Controller\Adminhtml\Customer;

use Amasty\Gdpr\Controller\Adminhtml\Customer\Anonymise;
use Amasty\Gdpr\Model\Anonymizer;
use Amasty\Gdpr\Test\Unit\Traits\ReflectionTrait;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\Manager;
use Magento\Store\App\Response\Redirect;
use PHPUnit\Framework\MockObject\Matcher\InvokedRecorder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers Anonymise
 */
class AnonymiseTest extends TestCase
{
    use ReflectionTrait;

    const TEST_NON_COMPLETE_ORDERS = [1, 2, 3];

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var Redirect|MockObject
     */
    private $redirectMock;

    /**
     * @var Manager|MockObject
     */
    private $messageManagerMock;

    /**
     * @var Anonymizer|MockObject
     */
    private $anonymizerMock;

    /**
     * @var Anonymise
     */
    private $controller;

    protected function setUp(): void
    {
        $this->requestMock = $this->createPartialMock(Http::class, ['getParam']);
        $this->redirectMock =  $this->createConfiguredMock(Redirect::class, ['getRefererUrl' => '']);
        $this->messageManagerMock = $this->createPartialMock(Manager::class, ['addWarningMessage']);
        $this->anonymizerMock = $this->createPartialMock(
            Anonymizer::class,
            ['anonymizeCustomer', 'getCustomerActiveOrders']
        );
    }

    /**
     * @param int
     * @param bool $hasActiveRegistry
     * @param array $nonCompleteOrders
     * @param InvokedRecorder $callAnonymize
     * @param InvokedRecorder $callAddWarning
     *
     * @dataProvider executeDataProvider
     * @covers Anonymise::execute
     */
    public function testExecute($customerId, $hasActiveRegistry, $nonCompleteOrders, $callAnonymize, $callAddWarning)
    {
        $redirectMock =  $this->createConfiguredMock(Redirect::class, ['getRefererUrl' => '']);
        $controllerMock = $this->createPartialMock(
            Anonymise::class,
            ['isCustomerHasActiveGiftRegistry', 'getNonCompletedOrderIds', '_redirect']
        );
        $this->setProperty($controllerMock, '_redirect', $this->redirectMock);
        $this->setProperty($controllerMock, '_request', $this->requestMock);
        $this->setProperty($controllerMock, 'anonymizer', $this->anonymizerMock);
        $this->setProperty($controllerMock, 'messageManager', $this->messageManagerMock);

        $this->requestMock
            ->expects($this->any())
            ->method('getParam')
            ->with('customerId')
            ->willReturn($customerId);

        $controllerMock
            ->expects($this->any())
            ->method('isCustomerHasActiveGiftRegistry')
            ->willReturn($hasActiveRegistry);

        $controllerMock
            ->expects($this->any())
            ->method('getNonCompletedOrderIds')
            ->willReturn($nonCompleteOrders);

        $this->messageManagerMock
            ->expects($callAddWarning)
            ->method('addWarningMessage');

        $this->anonymizerMock
            ->expects($callAnonymize)
            ->method('anonymizeCustomer')
            ->with($customerId);

        $controllerMock
            ->expects($this->once())
            ->method('_redirect')
            ->willReturn($redirectMock);

        $this->assertEquals($redirectMock, $controllerMock->execute());
    }

    public function executeDataProvider(): array
    {
        return [
            [1, false, [], $this->once(), $this->never()],
            [1, true, [], $this->never(), $this->once()],
            [1, false, self::TEST_NON_COMPLETE_ORDERS, $this->never(), $this->once()],
            [1, true, self::TEST_NON_COMPLETE_ORDERS, $this->never(), $this->once()],
            [0, false, [], $this->never(), $this->never()],
            [0, false, [], $this->never(), $this->never()],
            [0, true, self::TEST_NON_COMPLETE_ORDERS, $this->never(), $this->never()],
        ];
    }
}
