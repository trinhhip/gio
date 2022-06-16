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
use Mirasvit\GoogleTagManager\Model\DataProvider;
use Mirasvit\GoogleTagManager\Registry;
use Mirasvit\GoogleTagManager\Service\DataService;

class ViewCart extends Template
{
    private $checkoutSession;

    private $config;

    private $dataLayer;

    private $dataProvider;

    private $dataService;

    private $registry;

    public function __construct(
        CheckoutSession $checkoutSession,
        Config $config,
        DataLayer $dataLayer,
        DataProvider $dataProvider,
        DataService $dataService,
        Registry $registry,
        Template\Context $context
    ) {
        $this->checkoutSession   = $checkoutSession;
        $this->config            = $config;
        $this->dataLayer         = $dataLayer;
        $this->dataProvider      = $dataProvider;
        $this->dataService       = $dataService;
        $this->registry          = $registry;

        parent::__construct($context);
    }

    public function toHtml(): string
    {
        $quote = $this->checkoutSession->getQuote();

        $items = [];
        $index = 0;

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllItems() as $item) {
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

            $data = $this->dataService->getProductData($product, $quote->getQuoteCurrencyCode());

            $data['index'] = ++$index;

            $items[] = $data;
        }

        $data = [
            0 => 'event',
            1 => 'view_cart',
            2 => [
                'currency' => $quote->getQuoteCurrencyCode(),
                'value'    => $quote->getGrandTotal(),
                'items'    => $items,
            ],
        ];

        $this->dataLayer->setCatalogData($data);

        return '';
    }
}
