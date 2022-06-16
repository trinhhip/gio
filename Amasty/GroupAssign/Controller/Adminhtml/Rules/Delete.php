<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Controller\Adminhtml\Rules;

use Amasty\GroupAssign\Controller\Adminhtml\AbstractRules;
use Amasty\GroupAssign\Api\RuleRepositoryInterface;
use Amasty\GroupAssign\Model\RuleFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class Delete extends AbstractRules
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        RuleRepositoryInterface $ruleRepository,
        RuleFactory $ruleFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->coreRegistry = $coreRegistry;
        $this->ruleRepository = $ruleRepository;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Delete action
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        if ($id) {
            try {
                $this->ruleRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The rule has been deleted.'));
                $this->_redirect('amasty_groupassign/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete rule right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('amasty_groupassign/*/edit', ['id' => $id]);

                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Can\'t find a rule to delete.'));

        $this->_redirect('amasty_groupassign/*/');
    }
}
