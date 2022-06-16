<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-gtm
 * @version   1.0.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\GoogleTagManager\Block\Event;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\OrderRepository;
use Mirasvit\GoogleTagManager\Model\Config;
use Mirasvit\GoogleTagManager\Model\DataLayer;
use Mirasvit\GoogleTagManager\Service\DataService;

class Purchase extends Template
{
    private $checkoutSession;

    private $config;

    private $dataLayer;

    private $dataService;

    private $itemRepository;

    private $orderRepository;

    public function __construct(
        CheckoutSession $checkoutSession,
        Config $config,
        OrderItemRepositoryInterface $itemRepository,
        OrderRepository $orderRepository,
        DataService $dataService,
        DataLayer $dataLayer,
        Template\Context $context
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->config          = $config;
        $this->dataLayer       = $dataLayer;
        $this->dataService     = $dataService;
        $this->itemRepository  = $itemRepository;
        $this->orderRepository = $orderRepository;

        parent::__construct($context);
    }

    public function toHtml(): string
    {
        $lastOrderId = (int)$this->checkoutSession->getLastOrderId();

        try {
            $order = $this->orderRepository->get($lastOrderId);
        } catch (\Exception $e) {
            return '';
        }

        $items = [];
        $index = 1;
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getItems() as $item) {
            // skip parents
            if ($item->getChildren()) {
                continue;
            }

            $product = $item->getProduct();
            $product->setQuantity($item->getQtyOrdered());

            if ($item->getParentItemId()) {
                $parentItem = $item->getParentItem();

                $parentProduct = $parentItem->getProduct();

                $product->setParentSku($parentProduct->getSku());
                $product->setParentId($parentProduct->getId());
            }

            $productData = $this->dataService->getProductData($product, $order->getOrderCurrencyCode());

            $productData['index'] = $index++;

            $items[] = $productData;
        }

        $data = [
            0 => 'event',
            1 => 'purchase',
            2 => [
                'transaction_id' => $order->getIncrementId(),
                'value'          => $order->getGrandTotal(),
                'currency'       => $order->getOrderCurrencyCode(),
                'coupon'         => $order->getCouponCode(),
                'items'          => $items,
            ],
        ];

        $this->dataLayer->setCheckoutData($data);

        return '';
    }
}
