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
use Mirasvit\GoogleTagManager\Model\Config;
use Mirasvit\GoogleTagManager\Model\DataLayer;
use Mirasvit\GoogleTagManager\Service\DataService;

class BeginCheckout extends Template
{
    private $checkoutSession;

    private $config;

    private $dataLayer;

    private $dataService;

    public function __construct(
        CheckoutSession $checkoutSession,
        Config $config,
        DataLayer $dataLayer,
        DataService $dataService,
        Template\Context $context
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->config          = $config;
        $this->dataLayer       = $dataLayer;
        $this->dataService     = $dataService;

        parent::__construct($context);
    }

    public function toHtml(): string
    {
        $quote = $this->checkoutSession->getQuote();

        $items = [];
        $index = 1;
        foreach ($quote->getAllVisibleItems() as $item) {
            // skip parents
            if ($item->getChildren()) {
                continue;
            }

            $product = $item->getProduct();
            $product->setQuantity($item->getQty());

            if ($item->getParentItemId()) {
                $parentItem = $quote->getItemById($item->getParentItemId());

                $parentProduct = $parentItem->getProduct();

                $product->setParentSku($parentProduct->getSku());
                $product->setParentId($parentProduct->getId());
            }

            $productData = $this->dataService->getProductData($product, $quote->getQuoteCurrencyCode());

            $productData['index'] = $index++;

            $items[] = $productData;
        }

        $data = [
            0 => 'event',
            1 => 'begin_checkout',
            2 => [
                'coupon'   => $quote->getCouponCode(),
                'currency' => $quote->getQuoteCurrencyCode(),
                'value'    => $this->dataService->formatPrice((float)$quote->getGrandTotal()),
                'items'    => $items,
            ],
        ];

        $this->dataLayer->setCheckoutData($data);

        return '';
    }
}
