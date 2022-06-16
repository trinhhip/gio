<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/B2C.
 *
 * OmnyfyCustomzation/B2C is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OmnyfyCustomzation\B2C\Model\Product\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;

class ForRetail extends AbstractSource
{
    const NO = 1;
    const YES = 2;

    private AttributeFactory $eavAttrEntity;

    public function __construct(
        AttributeFactory $eavAttrEntity
    )
    {
        $this->eavAttrEntity = $eavAttrEntity;
    }

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['label' => __('Yes'), 'value' => self::YES],
            ['label' => __('No'), 'value' => self::NO]
        ];
        return $this->_options;
    }

    /**
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => $attributeCode . ' column',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = [];

        $index = 'IDX_' . strtoupper($this->getAttribute()->getAttributeCode());
        $indexes[$index] = ['type' => 'index', 'fields' => [$this->getAttribute()->getAttributeCode()]];

        return $indexes;
    }

    /**
     * @param int $store
     * @return Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->eavAttrEntity->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
