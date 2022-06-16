<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Controller\Adminhtml\Consents;

use Amasty\Gdpr\Controller\Adminhtml\AbstractConsents;
use Amasty\Gdpr\Model\Consent\Repository;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Edit extends AbstractConsents
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        Context $context,
        Repository $repository
    ) {
        $this->repository = $repository;

        parent::__construct($context);
    }

    /**
     * @return Page|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $consentId = (int)$this->getRequest()->getParam('id');

        if ($consentId) {
            try {
                $this->repository->getById($consentId);
                $resultPage->getConfig()->getTitle()->prepend(__('Edit Consent'));
                $resultPage->getLayout()->addBlock(
                    \Magento\Backend\Block\Store\Switcher::class,
                    'store_switcher',
                    'page.main.actions'
                );
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This consent no longer exists...'));

                return $this->_redirect('*/*/index');
            }
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Consent'));
        }

        return $resultPage;
    }
}
