<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Sidebar;

use Magento\Cms\Model\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Tag\Collection;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Tag\CollectionFactory;

/**
 * Cms tag claud sidebar block
 */
class TagClaud extends Template
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'tag_claud';

    /**
     * @var CollectionFactory
     */
    protected $_tagCollectionFactory;

    /**
     * @var Collection
     */
    protected $_tags;

    /**
     * @var int
     */
    protected $_maxCount;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param CollectionFactory $_tagCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $tagCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_tagCollectionFactory = $tagCollectionFactory;
    }

    /**
     * Retrieve tag class
     * @return array
     */
    public function getTagClass($tag)
    {
        $maxCount = $this->getMaxCount();
        $percent = floor(($tag->getCount() / $maxCount) * 100);

        if ($percent < 20) {
            return 'smallest';
        }
        if ($percent >= 20 and $percent < 40) {
            return 'small';
        }
        if ($percent >= 40 and $percent < 60) {
            return 'medium';
        }
        if ($percent >= 60 and $percent < 80) {
            return 'large';
        }
        return 'largest';
    }

    /**
     * Retrieve max tag number
     * @return array
     */
    public function getMaxCount()
    {
        if ($this->_maxCount == null) {
            $this->_maxCount = 0;
            foreach ($this->getTags() as $tag) {
                $count = $tag->getCount();
                if ($count > $this->_maxCount) {
                    $this->_maxCount = $count;
                }
            }
        }
        return $this->_maxCount;
    }

    /**
     * Retrieve tags
     * @return array
     */
    public function getTags()
    {
        if ($this->_tags === null) {
            $this->_tags = $this->_tagCollectionFactory->create();
            $resource = $this->_tags->getResource();
            $this->_tags->getSelect()->joinLeft(
                ['pt' => $resource->getTable('omnyfy_cms_article_tag')],
                'main_table.tag_id = pt.tag_id',
                []
            )->joinLeft(
                ['p' => $resource->getTable('omnyfy_cms_article')],
                'p.article_id = pt.article_id',
                []
            )->joinLeft(
                ['ps' => $resource->getTable('omnyfy_cms_article_store')],
                'p.article_id = pt.article_id',
                ['count' => 'count(main_table.tag_id)']
            )->group(
                'main_table.tag_id'
            )->where(
                'ps.store_id IN (?)',
                [0, (int)$this->_storeManager->getStore()->getId()]
            );
        }

        return $this->_tags;
    }

    /**
     * Retrieve block identities
     * @return array
     */
    public function getIdentities()
    {
        return [Block::CACHE_TAG . '_cms_tag_claud_widget'];
    }

}
