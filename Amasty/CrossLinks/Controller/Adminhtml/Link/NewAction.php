<?php

namespace Amasty\CrossLinks\Controller\Adminhtml\Link;

/**
 * Class NewAction
 * @package Amasty\CrossLinks\Controller\Adminhtml\Link
 */
class NewAction extends \Amasty\CrossLinks\Controller\Adminhtml\Link
{
    /**
     * Create new link
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
