<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use OmnyfyCustomzation\CmsBlog\Model\Url;

/**
 * Cms observer
 */
class PageBlockHtmlTopmenuBethtmlBeforeObserver implements ObserverInterface
{
    /**
     * Show top menu item config path
     */
    const XML_PATH_TOP_MENU_SHOW_ITEM = 'mfcms/top_menu/show_item';

    /**
     * Top menu item text config path
     */
    const XML_PATH_TOP_MENU_ITEM_TEXT = 'mfcms/top_menu/item_text';

    /**
     * @var Url
     */
    protected $_url;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param Url $url
     */
    public function __construct(
        Url $url,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_url = $url;
    }

    /**
     * Page block html topmenu gethtml before
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if (!$this->_scopeConfig->isSetFlag(static::XML_PATH_TOP_MENU_SHOW_ITEM, ScopeInterface::SCOPE_STORE)) {
            return;
        }

        /** @var Node $menu */
        $menu = $observer->getMenu();
        $block = $observer->getBlock();

        $tree = $menu->getTree();
        $data = [
            'name' => $this->_scopeConfig->getValue(static::XML_PATH_TOP_MENU_ITEM_TEXT, ScopeInterface::SCOPE_STORE),
            'id' => 'omnyfy-cms',
            'url' => $this->_url->getBaseUrl(),
            'is_active' => ($block->getRequest()->getModuleName() == 'cms'),
        ];
        $node = new Node($data, 'id', $tree, $menu);
        $menu->addChild($node);
    }
}
