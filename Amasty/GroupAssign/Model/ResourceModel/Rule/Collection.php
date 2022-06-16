<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Model\ResourceModel\Rule;

use Amasty\GroupAssign\Model\Rule;
use Amasty\GroupAssign\Model\ResourceModel\Rule as RuleResource;
use Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection;

/**
 * @method Rule[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(Rule::class, RuleResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
