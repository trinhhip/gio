<?php

declare(strict_types=1);

namespace Amasty\SeoToolKit\Controller\Adminhtml\Redirect;

class NewAction extends AbstractAction
{
    /**
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
