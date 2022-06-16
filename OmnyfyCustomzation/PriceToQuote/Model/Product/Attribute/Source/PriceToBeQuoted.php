<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/PriceToQuote.
 *
 * OmnyfyCustomzation/PriceToQuote is free software: you can redistribute it and/or modify
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

namespace OmnyfyCustomzation\PriceToQuote\Model\Product\Attribute\Source;

class PriceToBeQuoted extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            [
                'value' => 0,
                'label' => __('No')
            ],
            [
                'value' => 1,
                'label' => __('Yes')
            ]
        ];
        return $this->_options;
    }
}