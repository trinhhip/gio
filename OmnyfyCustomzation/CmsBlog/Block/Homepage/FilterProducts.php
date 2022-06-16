<?php
/**
 * Project: Filter Products on homepage
 * Author: seth
 * Date: 21/5/20
 * Time: 3:23 pm
 **/

namespace OmnyfyCustomzation\CmsBlog\Block\Homepage;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\View\Element\Template;

class FilterProducts extends Template
{
    protected $_collection;

    /**
     * FilterProducts constructor.
     * @param CollectionFactory $collectionFactory
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Template\Context $context,
        array $data = []
    )
    {
        $this->_collection = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param int $level
     * @return mixed
     */
    public function getCategoriesByLevel($level = 2)
    {
        $categories = $this->_collection->create();
        $categories->addAttributeToSelect('*');
        $categories->addAttributeToFilter('level', $level);
        $categories->addFieldToFilter('is_active', 1);
        return $categories;
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('cms/homepage/getcategories');
    }
}
