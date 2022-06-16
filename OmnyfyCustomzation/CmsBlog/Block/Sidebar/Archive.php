<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Sidebar;

use Magento\Cms\Model\Block;
use OmnyfyCustomzation\CmsBlog\Block\Article\ArticleList\AbstractList;
use OmnyfyCustomzation\CmsBlog\Model\Url;

/**
 * Cms sidebar archive block
 */
class Archive extends AbstractList
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'archive';

    /**
     * Available months
     * @var array
     */
    protected $_months;

    /**
     * Retrieve available months
     * @return array
     */
    public function getMonths()
    {
        if (is_null($this->_months)) {
            $this->_months = [];
            $this->_prepareArticleCollection();
            foreach ($this->_articleCollection as $article) {
                $time = strtotime($article->getData('publish_time'));
                $this->_months[date('Y-m', $time)] = $time;
            }
        }


        return $this->_months;
    }

    /**
     * Prepare articles collection
     *
     * @return void
     */
    protected function _prepareArticleCollection()
    {
        parent::_prepareArticleCollection();
        $this->_articleCollection->getSelect()->group(
            'MONTH(main_table.publish_time)',
            'DESC'
        );
    }

    /**
     * Retrieve year by time
     * @param int $time
     * @return string
     */
    public function getYear($time)
    {
        return date('Y', $time);
    }

    /**
     * Retrieve month by time
     * @param int $time
     * @return string
     */
    public function getMonth($time)
    {
        return __(date('F', $time));
    }

    /**
     * Retrieve archive url by time
     * @param int $time
     * @return string
     */
    public function getTimeUrl($time)
    {
        return $this->_url->getUrl(
            date('Y-m', $time),
            Url::CONTROLLER_ARCHIVE
        );
    }

    /**
     * Retrieve cms identities
     * @return array
     */
    public function getIdentities()
    {
        return [Block::CACHE_TAG . '_cms_archive_widget'];
    }

}
