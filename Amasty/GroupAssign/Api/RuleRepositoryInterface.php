<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Api;

interface RuleRepositoryInterface
{
    /**
     * Save Rule
     *
     * @param \Amasty\GroupAssign\Api\Data\RuleInterface $rule
     *
     * @return \Amasty\GroupAssign\Api\Data\RuleInterface
     */
    public function save(\Amasty\GroupAssign\Api\Data\RuleInterface $rule);

    /**
     * Get rule by id
     *
     * @param int $ruleId
     *
     * @return \Amasty\GroupAssign\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($ruleId);

    /**
     * Delete Rule
     *
     * @param \Amasty\GroupAssign\Api\Data\RuleInterface $rule
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GroupAssign\Api\Data\RuleInterface $rule);

    /**
     * Delete rule by id
     *
     * @param int $ruleId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($ruleId);

    /**
     * Get all active rules
     *
     * @return \Amasty\GroupAssign\Api\Data\RuleInterface[]
     */
    public function getActiveRules();

    /**
     * Get rule by name
     *
     * @param string $ruleName
     *
     * @return \Amasty\GroupAssign\Api\Data\RuleInterface
     */
    public function getRuleByName($ruleName);
}
