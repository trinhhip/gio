<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Api\Data;

interface RuleInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';

    const RULE_NAME = 'rule_name';

    const MOVE_TO_GROUP = 'move_to_group';

    const PRIORITY = 'priority';

    const STATUS = 'status';

    const CONDITIONS_SERIALIZED = 'conditions_serialized';

    /**#@-*/

    /**
     * @return string
     */
    public function getRuleName();

    /**
     * @param string $ruleName
     *
     * @return \Amasty\GroupAssign\Api\Data\RuleInterface
     */
    public function setRuleName($ruleName);

    /**
     * @return int
     */
    public function getMoveToGroup();

    /**
     * @param int $group
     *
     * @return \Amasty\GroupAssign\Api\Data\RuleInterface
     */
    public function setMoveToGroup($group);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $priority
     *
     * @return \Amasty\GroupAssign\Api\Data\RuleInterface
     */
    public function setPriority($priority);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\GroupAssign\Api\Data\RuleInterface
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getConditionsSerialized();

    /**
     * @param int $conditions
     *
     * @return \Amasty\GroupAssign\Api\Data\RuleInterface
     */
    public function setConditionsSerialized($conditions);
}
