<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Model\Rule\Condition\Customer;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Amasty\GroupAssign\Model\Rule\Condition\CustomerFactory
     */
    private $conditionCustomerFactory;

    /**
     * @var \Amasty\GroupAssign\Model\Rule\Condition\OrderFactory
     */
    private $conditionOrderFactory;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Amasty\GroupAssign\Model\Rule\Condition\CustomerFactory $conditionCustomerFactory,
        \Amasty\GroupAssign\Model\Rule\Condition\OrderFactory $conditionOrderFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->conditionCustomerFactory = $conditionCustomerFactory;
        $this->setType(\Amasty\GroupAssign\Model\Rule\Condition\Customer\Combine::class);
        $this->conditionOrderFactory = $conditionOrderFactory;
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $options = parent::getNewChildSelectOptions();

        /** @var \Amasty\GroupAssign\Model\Rule\Condition\Customer $condition */
        $conditionCustomer = $this->conditionCustomerFactory->create();
        $conditionCustomerAttributes = $conditionCustomer->loadAttributeOptions()->getAttributeOption();

        /** @var \Amasty\GroupAssign\Model\Rule\Condition\Order $conditionOrder */
        $conditionOrder = $this->conditionOrderFactory->create();
        $conditionOrderAttributes = $conditionOrder->loadAttributeOptions()->getAttributeOption();
        $options[] = [
            'value' => 'Amasty\GroupAssign\Model\Rule\Condition\Customer\Combine',
            'label' => __('Conditions Combination'),
        ];
        $customerAttributes = [];
        $orderAttributes = [];

        foreach ($conditionCustomerAttributes as $code => $label) {
            if ($code == 'lock_expires') {
                $label = 'Lock Expire';
            }
            $customerAttributes[] = [
                'value' => 'Amasty\GroupAssign\Model\Rule\Condition\Customer' . '|' . $code,
                'label' => $label,
            ];
        }

        foreach ($conditionOrderAttributes as $code => $label) {
            $orderAttributes[] = [
                'value' => 'Amasty\GroupAssign\Model\Rule\Condition\Order' . '|' . $code,
                'label' => $label
            ];
        }

        $options[] = [
            'value' => $customerAttributes,
            'label' => __('Customer attributes'),
        ];
        $options[] = [
            'value' => $orderAttributes,
            'label' => __('Order attributes')
        ];

        return $options;
    }

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var \Amasty\GroupAssign\Model\Rule\Condition\Customer $condition */
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }
}
