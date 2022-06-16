<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Controller\Adminhtml\Rules;

use Amasty\GroupAssign\Api\RuleRepositoryInterface;
use Amasty\GroupAssign\Model\ResourceModel\Rule\Collection;
use Amasty\GroupAssign\Model\ResourceModel\Rule\CollectionFactory;
use Amasty\GroupAssign\Controller\Adminhtml\AbstractRules;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends AbstractRules
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CollectionFactory
     */
    private $rulesCollectionFactory;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        Context $context,
        Filter $filter,
        LoggerInterface $logger,
        CollectionFactory $rulesCollectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->logger = $logger;
        $this->rulesCollectionFactory = $rulesCollectionFactory;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * Mass action execution
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider();

        /** @var Collection $collection */
        $collection = $this->filter->getCollection($this->rulesCollectionFactory->create());
        $deletedRules = 0;
        $failedRules = 0;

        if ($collection->count() > 0) {
            foreach ($collection->getItems() as $rule) {
                try {
                    $this->ruleRepository->delete($rule);
                    $deletedRules++;
                } catch (LocalizedException $e) {
                    $failedRules++;
                } catch (\Exception $e) {
                    $this->logger->error($e);
                    $failedRules++;
                }
            }
        }

        if ($deletedRules !== 0) {
            $this->messageManager->addSuccessMessage(
                __('%1 rule(s) has been successfully deleted', $deletedRules)
            );
        }

        if ($failedRules !== 0) {
            $this->messageManager->addErrorMessage(
                __('%1 rule(s) has been failed to delete', $failedRules)
            );
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
