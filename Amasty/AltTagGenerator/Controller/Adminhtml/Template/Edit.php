<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Controller\Adminhtml\Template;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_AltTagGenerator::edit';

    /**
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->updateTitles($resultPage);

        return $resultPage;
    }

    private function updateTitles(Page $page): void
    {
        if ($this->getRequest()->getParam(TemplateInterface::ID) === null) {
            $title = __('New Rule')->render();
        } else {
            $title = __('Edit Rule')->render();
        }

        $page->setActiveMenu('Amasty_AltTagGenerator::template')
            ->addBreadcrumb($title, $title);
        $page->getConfig()->getTitle()->prepend($title);
    }
}
