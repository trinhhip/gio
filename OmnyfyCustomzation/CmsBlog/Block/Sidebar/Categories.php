<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Sidebar;

use Magento\Cms\Model\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category\Collection;

/**
 * Cms sidebar categories block
 */
class Categories extends Template
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'categories';

    /**
     * @var Collection
     */
    protected $_categoryCollection;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param Collection $categoryCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Collection $categoryCollection,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_categoryCollection = $categoryCollection;
    }

    /**
     * Get grouped categories
     * @return Collection
     */
    public function getGroupedChilds()
    {
        $k = 'grouped_childs';
        if (!$this->hasDat($k)) {
            $array = $this->_categoryCollection
                ->addActiveFilter()
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->setOrder('position')
                ->getTreeOrderedArray();

            $this->setData($k, $array);
        }

        return $this->getData($k);
    }


    /**
     * Retrieve block identities
     * @return array
     */
    public function getIdentities()
    {
        return [Block::CACHE_TAG . '_cms_categories_widget'];
    }
}
