<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Model\Rule\Condition\Customer;

use Amasty\Groupcat\Model\Rule\Condition;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var Condition\CustomerFactory
     */
    private $conditionCustomerFactory;

    /**
     * @var Condition\TooltipRendererFactory
     */
    private $tooltipRendererFactory;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Amasty\Groupcat\Model\Rule\Condition\CustomerFactory $conditionFactory,
        Condition\TooltipRendererFactory $tooltipRendererFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->conditionCustomerFactory = $conditionFactory;
        $this->tooltipRendererFactory = $tooltipRendererFactory;
        $this->setType(\Amasty\Groupcat\Model\Rule\Condition\Customer\Combine::class);
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $options = parent::getNewChildSelectOptions();

        /** @var Condition\Customer $condition */
        $condition = $this->conditionCustomerFactory->create();
        $conditionAttributes = $condition->loadAttributeOptions()->getAttributeOption();

        $options[] = [
            'value' => \Amasty\Groupcat\Model\Rule\Condition\Customer\Combine::class,
            'label' => __('Conditions Combination'),
        ];
        $attributes = [];
        foreach ($conditionAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Amasty\Groupcat\Model\Rule\Condition\Customer' . '|' . $code,
                'label' => $label,
            ];
        }
        $options[] = [
            'value' => $attributes,
            'label' => __('Customer attributes'),
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
            /** @var Condition\Customer $condition */
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }

    public function asHtml()
    {
        /** @var Condition\TooltipRenderer $tooltipRenderer */
        $tooltipRenderer = $this->tooltipRendererFactory->create(
            [
                'tooltipTemplate' => 'Amasty_Groupcat::rule/tooltip/customer.phtml'
            ]
        );

        return parent::asHtml() . $tooltipRenderer->renderTooltip();
    }
}
