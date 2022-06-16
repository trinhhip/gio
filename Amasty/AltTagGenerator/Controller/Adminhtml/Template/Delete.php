<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Controller\Adminhtml\Template;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\Template\Command\DeleteTemplateInterface;
use Amasty\AltTagGenerator\Model\Template\Query\GetByIdInterface;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_AltTagGenerator::template_delete';

    /**
     * @var GetByIdInterface
     */
    private $getById;

    /**
     * @var DeleteTemplateInterface
     */
    private $deleteTemplate;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        GetByIdInterface $getById,
        DeleteTemplateInterface $deleteTemplate,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
        $this->getById = $getById;
        $this->deleteTemplate = $deleteTemplate;
        $this->logger = $logger;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $ruleId = (int) $this->getRequest()->getParam(TemplateInterface::ID);
        if ($ruleId) {
            try {
                $template = $this->getById->execute($ruleId);
                $this->deleteTemplate->execute($template);
                $this->messageManager->addSuccessMessage(__('Rule was deleted successfully.'));
                return $redirect->setPath('*/*');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong. Please review the error log.'));
                $this->logger->error($e);

            }
            return $redirect->setRefererUrl();
        }

        return $redirect->setPath('*/*');
    }
}
