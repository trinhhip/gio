<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Omnyfy Cms Helper
 */
class Page extends AbstractHelper
{
    /**
     * Design package instance
     *
     * @var DesignInterface
     */
    protected $_design;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DesignInterface $design
     * @param TimezoneInterface $localeDate
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        DesignInterface $design,
        TimezoneInterface $localeDate,
        PageFactory $resultPageFactory
    )
    {
        $this->_design = $design;
        $this->_localeDate = $localeDate;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Return result cms page
     *
     * @param Action $action
     * @param AbstractModel $page
     * @return \Magento\Framework\View\Result\Page|bool
     */
    public function prepareResultPage(Action $action, $page)
    {
        if ($page->getCustomThemeFrom() && $page->getCustomThemeTo()) {
            $inRange = $this->_localeDate->isScopeDateInInterval(
                null,
                $page->getCustomThemeFrom(),
                $page->getCustomThemeTo()
            );
        } else {
            $inRange = false;
        }

        if ($page->getCustomTheme()) {
            if ($inRange) {
                $this->_design->setDesignTheme($page->getCustomTheme());
            }
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        if ($inRange
            && $page->getCustomLayout()
            && $page->getCustomLayout() != 'empty'
        ) {
            $handle = $page->getCustomLayout();
        } else {
            $handle = $page->getPageLayout();
        }
        if ($handle) {
            $resultPage->getConfig()->setPageLayout($handle);
        }

        $fullActionName = $action->getRequest()->getFullActionName();
        $resultPage->addHandle($fullActionName);
        $resultPage->addPageLayoutHandles(['id' => $page->getIdentifier()]);

        $this->_eventManager->dispatch(
            $fullActionName . '_render',
            ['page' => $page, 'controller_action' => $action]
        );

        if ($inRange && $page->getCustomLayoutUpdateXml()) {
            $layoutUpdate = $page->getCustomLayoutUpdateXml();
        } else {
            $layoutUpdate = $page->getLayoutUpdateXml();
        }
        if ($layoutUpdate) {
            $resultPage->getLayout()->getUpdate()->addUpdate($layoutUpdate);
        }

        return $resultPage;
    }

}
