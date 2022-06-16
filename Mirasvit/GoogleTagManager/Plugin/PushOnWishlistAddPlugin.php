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

use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\GoogleTagManager\Model\DataLayer;
use Mirasvit\GoogleTagManager\Service\DataService;

/**
 * @see \Magento\Wishlist\Model\Wishlist::addNewItem()
 */
class PushOnWishlistAddPlugin
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
     * @param Wishlist    $subject
     * @param Item|string $result
     *
     * @return Item|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterAddNewItem(Wishlist $subject, $result)
    {
        $store = $this->storeManager->getStore($result->getStoreId());

        // error message
        if (is_string($result)) {
            return $result;
        }

        $data = [
            0 => 'event',
            1 => 'add_to_wishlist',
            2 => [
                'currency' => $store->getCurrentCurrency()->getCode(),
                'value'    => $result->getProduct()->getFinalPrice($result->getQty()),
                'items'    => [
                    $this->dataService->getProductData($result->getProduct(), $store->getCurrentCurrency()->getCode()),
                ],
            ],
        ];

        $this->dataLayer->setCheckoutData($data);

        return $result;
    }
}
