<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Block\Adminhtml\Rules\Edit\Tab;

use Amasty\GroupAssign\Model\Rule;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Rule\Model\Condition\AbstractCondition;

class Conditions extends Generic
{
    /**
     * Block name in layout
     *
     * @var string
     */
    protected $_nameInLayout = 'conditions';

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    private $conditionsBlock;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    private $fieldset;

    /**
     * @var Rule
     */
    private $rule;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $metadata;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $fieldset,
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        array $data = []
    ) {
        $this->rule = $registry->registry(Rule::CURRENT_GROUPASSIGN_RULE);
        $this->conditionsBlock = $conditions;
        $this->formFactory = $formFactory;
        $this->fieldset = $fieldset;
        $this->metadata = $metadata;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        if (version_compare($this->metadata->getVersion(), '2.2.0', '>=')) {
            //Fix for Magento >2.2.0 to display right form layout.
            //Result of compatibility with 2.1.x.
            $this->_prepareLayout();
        }
        $conditionsFieldSetId = Rule::FORM_NAMESPACE
            . 'rule_conditions_fieldset_' . $this->rule->getId();
        $newChildUrl = $this->getUrl(
            'amasty_groupassign/rules/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => Rule::FORM_NAMESPACE]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->formFactory->create();
        $renderer = $this->fieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);
        $fieldset = $form->addFieldset(
            $conditionsFieldSetId,
            []
        )->setRenderer(
            $renderer
        );
        $fieldset->addField(
            'conditions' . $conditionsFieldSetId,
            'text',
            [
                'name' => 'conditions' . $conditionsFieldSetId,
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true,
                'data-form-part' => Rule::FORM_NAMESPACE,
            ]
        )->setRule($this->rule)->setRenderer($this->conditionsBlock);
        $form->setValues($this->rule->getData());
        $this->setConditionFormName($this->rule->getConditions(), Rule::FORM_NAMESPACE);

        return $form->toHtml();
    }

    /**
     * @param AbstractCondition $abstractConditions
     * @param string $formName
     *
     * @return void
     */
    private function setConditionFormName(AbstractCondition $abstractConditions, $formName)
    {
        $abstractConditions->setFormName($formName);
        $conditions = $abstractConditions->getConditions();

        if ($conditions && is_array($conditions)) {
            foreach ($conditions as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }
}
