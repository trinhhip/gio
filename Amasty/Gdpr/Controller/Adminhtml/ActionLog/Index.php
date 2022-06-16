<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\ActionLog;

use Amasty\Gdpr\Controller\Adminhtml\AbstractActionLog;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractActionLog
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Gdpr::action_log');
        $resultPage->getConfig()->getTitle()->prepend(__('Action Log'));
        $resultPage->addBreadcrumb(__('Action Log'), __('Action Log'));

        return $resultPage;
    }
}
