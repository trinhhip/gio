<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */

declare(strict_types=1);

namespace Amasty\GroupAssign\Cron;

use Amasty\GroupAssign\Api\RuleRepositoryInterface;
use Amasty\GroupAssign\Model\Rule;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class GroupsUpdate
{
    const PATH_TO_MODULE_ENABLED = 'amasty_groupassign/general/enabled';

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CollectionFactory
     */
    private $customerCollection;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var array
     */
    private $enablingConfigByStore;

    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        CustomerRepository $customerRepository,
        CollectionFactory $customerCollection,
        ScopeConfigInterface $scopeConfig,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->customerRepository = $customerRepository;
        $this->customerCollection = $customerCollection;
        $this->scopeConfig = $scopeConfig;
        $this->storeRepository = $storeRepository;
    }

    public function execute()
    {
        if (!$this->isModuleEnabledForAnyStore()) {
            return;
        }

        $allCustomers = $this->customerCollection->create();
        $activeRules = $this->ruleRepository->getActiveRules();

        foreach ($allCustomers as $customer) {
            $rulesToApply = [];

            /** @var Rule $rule */
            foreach ($activeRules as $rule) {
                if ($validate = $rule->getConditions()->validate($customer)) {
                    $rulesToApply[] = ['priority' => $rule->getPriority(), 'rule' => $rule];
                }
            }

            if (count($rulesToApply)
                && ($ruleToApply = $this->getRuleWithMaxPriority($rulesToApply))
                && $this->isModuleEnabledForStore((int)$customer->getData('store_id'))
            ) {
                $customer->setGroupId((int)$ruleToApply['rule']->getMoveToGroup());
            }
        }

        $allCustomers->save();
    }

    /**
     * Rules apply regarding the lowest priority.
     * I.e. rule with priority 1 will be applied instead of rule with priority 10.
     *
     * @param array $rules
     * @return array
     */
    private function getRuleWithMaxPriority(array $rules): array
    {
        usort($rules, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $rules[0];
    }

    private function isModuleEnabledForAnyStore(): bool
    {
        return in_array(true, $this->getEnablingConfigForAllStores());
    }

    private function isModuleEnabledForStore(int $storeId): bool
    {
        $configByStore = $this->getEnablingConfigForAllStores();

        return $configByStore[$storeId] ?? false;
    }

    private function getEnablingConfigForAllStores(): array
    {
        if ($this->enablingConfigByStore === null) {
            foreach ($this->storeRepository->getList() as $store) {
                $this->enablingConfigByStore[$store->getId()] = (bool)$this->scopeConfig->getValue(
                    self::PATH_TO_MODULE_ENABLED,
                    ScopeInterface::SCOPE_STORE,
                    $store->getId()
                );
            }
        }

        return $this->enablingConfigByStore;
    }
}
