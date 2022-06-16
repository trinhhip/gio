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

namespace Mirasvit\GoogleTagManager\Model\Event;

use Magento\Catalog\Model\ProductRepository;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\GoogleTagManager\Api\Data\EventInterface;
use Mirasvit\GoogleTagManager\Service\DataService;

class SelectItemEvent implements EventInterface
{
    private $dataService;

    private $productRepository;

    private $storeManager;

    public function __construct(
        DataService $dataService,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->dataService       = $dataService;
        $this->productRepository = $productRepository;
        $this->storeManager      = $storeManager;
    }

    public function getData(array $data): array
    {
        $product  = $this->productRepository->getById($data[EventInterface::ATTR_GTM_ITEM_ID]);
        $listId   = $data[EventInterface::ATTR_GTM_LIST_ID];
        $listName = isset($data[EventInterface::ATTR_GTM_LIST_NAME]) ?
            $data[EventInterface::ATTR_GTM_LIST_NAME] :
            $listId;

        $store = $this->storeManager->getStore();

        return [
            'event'          => 'select_item',
            'item_list_id'   => $listId,
            'item_list_name' => $listName,
            'ecommerce' => [
                'items' => [
                    $this->dataService->getProductData($product, $store->getCurrentCurrency()->getCode()),
                ],
            ],
        ];
    }
}
