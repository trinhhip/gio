<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Model;

use Amasty\GroupAssign\Api\Data\RuleInterface;
use Magento\Rule\Model\AbstractModel;

/**
 * @method \Amasty\GroupAssign\Model\Rule\Condition\Customer\Combine getActions()
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rule extends AbstractModel implements RuleInterface
{
    /**#@+
     * Constants
     */
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;

    const CURRENT_GROUPASSIGN_RULE = 'current_amasty_groupassign_rule';

    const FORM_NAMESPACE = 'amasty_groupassign_rules_form';

    /**#@-*/

    protected $_eventPrefix = 'groups_rule';

    protected $_eventObject = 'rule';

    /**
     * @var \Amasty\GroupAssign\Model\Rule\Condition\CombineFactory
     */
    protected $combineFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Amasty\GroupAssign\Model\Rule\Condition\Customer\CombineFactory $combineFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->combineFactory = $combineFactory;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Model Init
     *
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Rule::class);
        $this->setIdFieldName(RuleInterface::ID);
    }

    /**
     * Getter for rule conditions collection.
     *
     * @return \Amasty\Groupcat\Model\Rule\Condition\Customer\Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * Getter for rule actions collection.
     *
     * @return \Amasty\Groupcat\Model\Rule\Condition\Customer\Combine
     */
    public function getActionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getRuleName()
    {
        return $this->_getData(RuleInterface::RULE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setRuleName($ruleName)
    {
        $this->setData(RuleInterface::RULE_NAME, $ruleName);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMoveToGroup()
    {
        return $this->_getData(RuleInterface::MOVE_TO_GROUP);
    }

    /**
     * @inheritdoc
     */
    public function setMoveToGroup($group)
    {
        $this->setData(RuleInterface::MOVE_TO_GROUP, $group);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return $this->_getData(RuleInterface::PRIORITY);
    }

    /**
     * @inheritdoc
     */
    public function setPriority($priority)
    {
        $this->setData(RuleInterface::PRIORITY, $priority);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(RuleInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(RuleInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConditionsSerialized()
    {
        return $this->_getData(RuleInterface::CONDITIONS_SERIALIZED);
    }

    /**
     * @inheritdoc
     */
    public function setConditionsSerialized($conditions)
    {
        $this->setData(RuleInterface::CONDITIONS_SERIALIZED, $conditions);

        return $this;
    }
}
