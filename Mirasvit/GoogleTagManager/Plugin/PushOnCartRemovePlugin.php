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

namespace Mirasvit\GoogleTagManager\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Mirasvit\GoogleTagManager\Model\DataLayer;
use Mirasvit\GoogleTagManager\Service\DataService;

/**
 * @see \Magento\Quote\Model\Quote::removeItem()
 */
class PushOnCartRemovePlugin
{
    private $dataLayer;

    private $dataService;

    public function __construct(
        DataLayer $dataLayer,
        DataService $dataService
    ) {
        $this->dataLayer   = $dataLayer;
        $this->dataService = $dataService;
    }

    /**
     * @param Quote $subject
     * @param Quote $result
     * @param int   $itemId
     *
     * @return Quote
     */
    public function afterRemoveItem(Quote $subject, Quote $result, $itemId)
    {
        /** @var \Magento\Quote\Model\Quote\Item $result */
        $item = $subject->getItemById($itemId);

        $data = [
            0 => 'event',
            1 => 'remove_from_cart',
            2 => [
                'currency' => $subject->getQuoteCurrencyCode(),
                'value'    => $subject->getGrandTotal(),
                'items'    => [
                    $this->dataService->getProductData($item->getProduct(), $subject->getQuoteCurrencyCode()),
                ],
            ],
        ];

        $this->dataLayer->setCheckoutData($data);

        return $result;
    }
}
