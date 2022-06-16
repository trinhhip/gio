<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Controller\Adminhtml\Template;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\ResourceModel\Template\Collection;
use Amasty\AltTagGenerator\Model\ResourceModel\Template\CollectionFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

abstract class AbstractMassAction extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_AltTagGenerator::template';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var RuleRepositoryInterface
     */
    private $repository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        Context $context,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * @param TemplateInterface $template
     * @throws LocalizedException
     * @throws Exception
     */
    abstract protected function itemAction(TemplateInterface $template): void;

    /**
     * @return Redirect
     */
    public function execute()
    {
        $collection = $this->retrieveCollection();

        if ($collection && $collection->getSize()) {
            $updatedItems = 0;

            /** @var TemplateInterface $template */
            foreach ($collection->getItems() as $template) {
                try {
                    $this->itemAction($template);
                    $updatedItems++;
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage($this->getErrorMessage());
                    $this->logger->critical($e);
                }
            }

            $this->messageManager->addSuccessMessage($this->getSuccessMessage($updatedItems));
        }

        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $redirect->setRefererUrl();
    }

    private function retrieveCollection(): ?Collection
    {
        try {
            /** @var Collection $collection */
            $collection = $this->filter->getCollection($this->collectionFactory->create());
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $collection = null;
        }

        return $collection;
    }

    protected function getErrorMessage(): Phrase
    {
        return __('We can\'t change item right now. Please review the log and try again.');
    }

    protected function getSuccessMessage(int $collectionSize = 0): Phrase
    {
        if ($collectionSize) {
            return __('A total of %1 record(s) have been changed.', $collectionSize);
        }

        return __('No records have been changed.');
    }
}
