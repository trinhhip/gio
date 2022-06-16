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

use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;
use Mirasvit\GoogleTagManager\Model\DataLayer;
use Mirasvit\GoogleTagManager\Model\DataProvider;
use Mirasvit\GoogleTagManager\Registry;
use Mirasvit\GoogleTagManager\Service\DataService;

/**
 * @deprecated
 * @see vendor/mirasvit/module-gtm/src/GoogleTagManager/view/frontend/web/js/event/view-item-list.js
 */
class ViewList extends Template
{
    protected $dataLayer;

    protected $dataProvider;

    protected $dataService;

    protected $registry;

    public function __construct(
        DataLayer $dataLayer,
        DataProvider $dataProvider,
        DataService $dataService,
        Registry $registry,
        Template\Context $context
    ) {
        $this->dataLayer    = $dataLayer;
        $this->dataProvider = $dataProvider;
        $this->dataService  = $dataService;
        $this->registry     = $registry;

        parent::__construct($context);
    }

    public function toHtml(): string
    {
        $block    = $this->getListBlock();
        $category = $this->registry->getCategory();

        if (!$category || !$block || !($collection = $block->getLoadedProductCollection())) {
            return '';
        }

        $items = [];
        $index = 1;

        /** @var Product $product */
        foreach ($collection as $product) {
            $productData = $this->dataService->getProductData($product, $this->_storeManager->getStore()->getCurrentCurrency()->getCode());

            $this->dataProvider->addProduct($productData);

            $productData['item_list_name'] = $this->getItemListName();
            $productData['item_list_id']   = $this->getItemListId();
            $productData['index']          = $index++;

            $items[] = $productData;
        }

        $data = [
            0 => 'event',
            1 => 'view_item_list',
            2 => [
                'item_list_name' => $this->getItemListName(),
                'item_list_id'   => $this->getItemListId(),
                'items'          => $items,
            ],
        ];

        $this->dataLayer->setCatalogData($data);

        return '';
    }

    private function getListBlock(): ?BlockInterface
    {
        $blockName = (string)$this->getData('block_name');
        $block     = $this->getLayout()->getBlock($blockName);

        return $block ? : null;
    }

    private function getItemListName(): string
    {
        $listType = (string)$this->getData('list_type');

        if ($listType === 'category') {
            $currentCategory = $this->registry->getCategory();

            return $currentCategory ? $currentCategory->getName() : 'category';
        } elseif ($listType == 'search') {
            return 'Search Results';
        } else {
            return $listType;
        }
    }

    private function getItemListId(): string
    {
        $listType = (string)$this->getData('list_type');

        if ($listType === 'category') {
            $currentCategory = $this->registry->getCategory();

            return $currentCategory ? 'category_' . (string)$currentCategory->getId() : $listType;
        }

        return $listType;
    }
}
