<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Model\Repository;

use Amasty\GroupAssign\Api\RuleRepositoryInterface;
use Amasty\GroupAssign\Api\Data\RuleInterface;
use Amasty\GroupAssign\Model\Rule;
use Amasty\GroupAssign\Model\RuleFactory;
use Amasty\GroupAssign\Model\ResourceModel\Rule as RuleResource;
use Amasty\GroupAssign\Model\ResourceModel\Rule\Collection;
use Amasty\GroupAssign\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class RuleRepository implements RuleRepositoryInterface
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var RuleResource
     */
    private $ruleResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $rules;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        RuleFactory $ruleFactory,
        RuleResource $ruleResource,
        CollectionFactory $collectionFactory
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->ruleResource = $ruleResource;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(RuleInterface $rule)
    {
        try {
            if ($rule->getId()) {
                $rule = $this->getById($rule->getId())
                    ->addData($rule->getData());
            }
            $this->ruleResource->save($rule);
            unset($this->rules[$rule->getId()]);
        } catch (\Exception $e) {
            if ($rule->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save rule with ID %1. Error: %2',
                        [$rule->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new rule. Error: %1', $e->getMessage()));
        }

        return $rule;
    }

    /**
     * @inheritdoc
     */
    public function getById($ruleId)
    {
        if (!isset($this->rules[$ruleId])) {
            /** @var \Amasty\GroupAssign\Model\Rule $rule */
            $rule = $this->ruleFactory->create();
            $this->ruleResource->load($rule, $ruleId);

            if (!$rule->getId()) {
                throw new NoSuchEntityException(__('Rule with specified ID "%1" not found.', $ruleId));
            }
            $this->rules[$ruleId] = $rule;
        }

        return $this->rules[$ruleId];
    }

    /**
     * @inheritdoc
     */
    public function delete(RuleInterface $rule)
    {
        try {
            $this->ruleResource->delete($rule);
            unset($this->rules[$rule->getId()]);
        } catch (\Exception $e) {
            if ($rule->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove rule with ID %1. Error: %2',
                        [$rule->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove rule. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($ruleId)
    {
        $rule = $this->getById($ruleId);

        $this->delete($rule);
    }

    /**
     * @inheritdoc
     */
    public function getActiveRules()
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', Rule::STATUS_ENABLED);

        return $collection->getItems();
    }

    /**
     * @inheritdoc
     */
    public function getRuleByName($ruleName)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('rule_name', $ruleName);

        return $collection->getFirstItem();
    }
}
