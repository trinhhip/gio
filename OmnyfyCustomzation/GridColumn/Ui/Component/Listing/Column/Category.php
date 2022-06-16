<?php

namespace OmnyfyCustomzation\GridColumn\Ui\Component\Listing\Column;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Category extends Column
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;
    /**
     * @var CollectionFactory
     */
    protected $categoryCollectFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProductFactory $productFactory,
        CollectionFactory $categoryCollectFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->productFactory = $productFactory;
        $this->categoryCollectFactory = $categoryCollectFactory;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    public function prepareDataSource(array $dataSource)
    {
        $fieldName = $this->getData('name');
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $listCategories = [];
                $productId = $item['entity_id'];
                $product = $this->productFactory->create()->load($productId);
                $categoryIds = $product->getCategoryIds();
                $categories = $this->categoryCollectFactory->create();
                $categories->addFieldToSelect('name');
                $categories->addIdFilter($categoryIds);
                foreach ($categories as $category) {
                    $listCategories[] = $category->getName();
                }
                $item[$fieldName] = implode(', ', $listCategories);
            }
        }
        return $dataSource;
    }
}
