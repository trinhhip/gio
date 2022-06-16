<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Controller\Adminhtml\Template;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\Backend\Template\Initialization as TemplateInitialization;
use Amasty\AltTagGenerator\Model\Template\Command\Save as SaveTemplate;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = Edit::ADMIN_RESOURCE;

    const RULE_PERSISTENT_NAME = 'amasty_alt_template';

    /**
     * @var TemplateInitialization
     */
    private $templateInitialization;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var SaveTemplate
     */
    private $saveTemplate;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TemplateInitialization $templateInitialization,
        DataPersistorInterface $dataPersistor,
        SaveTemplate $saveTemplate,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
        $this->templateInitialization = $templateInitialization;
        $this->dataPersistor = $dataPersistor;
        $this->saveTemplate = $saveTemplate;
        $this->logger = $logger;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $inputTemplateData = $this->getTemplateData();

        try {
            $template = $this->templateInitialization->execute($inputTemplateData);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->getRedirect('*/*');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set(self::RULE_PERSISTENT_NAME, $inputTemplateData);
            $params = [];
            if ($templateId = $this->getTemplateId()) {
                $params[TemplateInterface::ID] = $templateId;
            }

            return $this->getRedirect('*/*/edit', $params);
        }

        try {
            $this->saveTemplate->execute($template);
            $this->messageManager->addSuccessMessage(__('Rule was saved successfully.'));
            if ($this->getRequest()->getParam('back')) {
                return $this->getRedirect('*/*/edit', [TemplateInterface::ID => $template->getId()]);
            } else {
                return $this->getRedirect('*/*');
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong. Please review the error log.'));
            $this->logger->error($e->getMessage());
        }

        $this->dataPersistor->set(self::RULE_PERSISTENT_NAME, $inputTemplateData);

        $params = [];
        if ($templateId = $this->getTemplateId()) {
            $params[TemplateInterface::ID] = $templateId;
        }

        return $this->getRedirect('*/*/edit', $params);
    }

    private function getTemplateId(): ?int
    {
        $ruleData = $this->getRequest()->getParam('template', []);
        return isset($ruleData[TemplateInterface::ID]) ? (int) $ruleData[TemplateInterface::ID] : null;
    }

    private function getTemplateData(): array
    {
        return (array) $this->getRequest()->getParam('template', []);
    }

    private function getRedirect(string $path = '', array $params = []): Redirect
    {
        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($path) {
            $redirect->setPath($path, $params);
        } else {
            $redirect->setRefererUrl();
        }

        return $redirect;
    }
}
