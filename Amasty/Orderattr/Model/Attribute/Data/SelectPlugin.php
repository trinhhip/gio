<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model\Attribute\Data;

use Amasty\Orderattr\Model\ResourceModel\Entity\Entity;
use Magento\Eav\Model\Attribute\Data\Select;

class SelectPlugin
{

    /**
     * @param Select $subject
     * @param Select $result
     * @param array|string $value
     * @return Select
     */
    public function afterCompactValue(Select $subject, Select $result, $value)
    {
        $attribute = $subject->getAttribute();

        if (($attribute->getEntityType()->getEntityTypeCode() === Entity::ENTITY_TYPE_CODE)
            && ($attribute->getFrontendInput() === 'checkboxes')
        ) {
            $subject->getEntity()->setData($attribute->getAttributeCode(), $value);
        }

        return $result;
    }
}
