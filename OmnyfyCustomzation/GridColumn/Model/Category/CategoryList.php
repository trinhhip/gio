<?php

namespace OmnyfyCustomzation\GridColumn\Model\Category;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class CategoryList implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    )
    {
        $this->categoryCollectionFactory = $collectionFactory;

    }

    public function toOptionArray($addEmpty = true)
    {

        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToSelect('name');
        $collection->setOrder('name', 'ASC');
        $options = [];
        if ($addEmpty) {
            $options[] = ['label' => __('-- Please Select a Category --'), 'value' => ''];
        }
        foreach ($collection as $category) {
            $options[] = ['label' => $category->getName(), 'value' => $category->getId()];
        }
        return $options;
    }
}
