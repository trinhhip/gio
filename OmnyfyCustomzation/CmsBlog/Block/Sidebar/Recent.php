<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Sidebar;

use Magento\Cms\Model\Block;
use Magento\Store\Model\ScopeInterface;
use OmnyfyCustomzation\CmsBlog\Block\Article\ArticleList\AbstractList;

/**
 * Cms sidebar categories block
 */
class Recent extends AbstractList
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'recent_articles';

    /**
     * @return $this
     */
    public function _construct()
    {
        $this->setPageSize(
            (int)$this->_scopeConfig->getValue(
                'mfcms/sidebar/' . $this->_widgetKey . '/articles_per_page',
                ScopeInterface::SCOPE_STORE
            )
        );
        return parent::_construct();
    }

    /**
     * Retrieve block identities
     * @return array
     */
    public function getIdentities()
    {
        return [Block::CACHE_TAG . '_cms_recent_articles_widget'];
    }

}
