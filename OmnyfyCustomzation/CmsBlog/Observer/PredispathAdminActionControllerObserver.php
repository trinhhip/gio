<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Observer;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use OmnyfyCustomzation\CmsBlog\Model\AdminNotificationFeed;
use OmnyfyCustomzation\CmsBlog\Model\AdminNotificationFeedFactory;

/**
 * Cms observer
 */
class PredispathAdminActionControllerObserver implements ObserverInterface
{
    /**
     * @var AdminNotificationFeedFactory
     */
    protected $_feedFactory;

    /**
     * @var Session
     */
    protected $_backendAuthSession;

    /**
     * @param AdminNotificationFeedFactory $feedFactory
     * @param Session $backendAuthSession
     */
    public function __construct(
        AdminNotificationFeedFactory $feedFactory,
        Session $backendAuthSession
    )
    {
        $this->_feedFactory = $feedFactory;
        $this->_backendAuthSession = $backendAuthSession;
    }

    /**
     * Predispath admin action controller
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->_backendAuthSession->isLoggedIn()) {
            $feedModel = $this->_feedFactory->create();
            /* @var $feedModel AdminNotificationFeed */
            $feedModel->checkUpdate();
        }
    }
}
