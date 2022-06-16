<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Controller\Adminhtml\Rules;

use Amasty\GroupAssign\Controller\Adminhtml\AbstractRules;
use Amasty\GroupAssign\Model\Repository\RuleRepository;
use Amasty\GroupAssign\Model\RuleFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

class Edit extends AbstractRules
{
    /**
     * @var RuleRepository
     */
    private $ruleRepository;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Context $context,
        RuleRepository $ruleRepository,
        RuleFactory $ruleFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->ruleRepository = $ruleRepository;
        $this->ruleFactory = $ruleFactory;
        $this->registry = $registry;
    }

    /**
     * Edit action
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $title = __('New Rule');

        if ($id) {
            $model = $this->ruleRepository->getById($id);
            $title = __('Edit Rule %1', $model->getName());
        } else {
            $model = $this->ruleFactory->create();
        }
        $this->registry->register(\Amasty\GroupAssign\Model\Rule::CURRENT_GROUPASSIGN_RULE, $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_GroupAssign::rules');
        $resultPage->addBreadcrumb(__('Rules'), __('Rules'));
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
