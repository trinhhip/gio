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
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\GoogleTagManager\Model\DataLayer;
use Mirasvit\GoogleTagManager\Service\DataService;

/**
 * @see \Magento\Quote\Model\Quote::addProduct()
 */
class PushOnCartAddPlugin
{
    private $dataLayer;

    private $dataService;

    private $storeManager;

    public function __construct(
        DataLayer $dataLayer,
        DataService $dataService,
        StoreManagerInterface $storeManager
    ) {
        $this->dataLayer    = $dataLayer;
        $this->dataService  = $dataService;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Quote $subject
     * @param Quote\Item|string $result
     *
     * @return Quote\Item|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterAddProduct(Quote $subject, $result)
    {
        if (!is_object($result)) {
            return $result;
        }

        /** @var \Magento\Quote\Model\Quote\Item $result */

        $currency = $subject->getQuoteCurrencyCode();
        if (!$currency) {
            $store    = $this->storeManager->getStore($result->getStoreId());
            $currency = $store->getCurrentCurrency()->getCode();
        }

        $product = $result->getProduct();
        $product->setQuantity($result->getQty());

        $data = [
            0 => 'event',
            1 => 'add_to_cart',
            2 => [
                'currency' => $subject->getQuoteCurrencyCode(),
                'value'    => $subject->getGrandTotal(),
                'items'    => [
                    $this->dataService->getProductData($product, $currency),
                ],
            ],
        ];

        $this->dataLayer->setCheckoutData($data);

        return $result;
    }
}
