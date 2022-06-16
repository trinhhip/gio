<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Sidebar;

use Magento\Store\Model\ScopeInterface;

/**
 * Cms sidebar widget trait
 */
trait Widget
{
    /**
     * Retrieve block sort order
     * @return int
     */
    public function getSortOrder()
    {
        if (!$this->hasData('sort_order')) {
            $this->setData('sort_order', $this->_scopeConfig->getValue(
                'mfcms/sidebar/' . $this->_widgetKey . '/sort_order', ScopeInterface::SCOPE_STORE
            ));
        }
        return (int)$this->getData('sort_order');
    }

    /**
     * Retrieve block html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_scopeConfig->getValue(
            'mfcms/sidebar/' . $this->_widgetKey . '/enabled', ScopeInterface::SCOPE_STORE
        )) {
            return parent::_toHtml();
        }

        return '';
    }
}
