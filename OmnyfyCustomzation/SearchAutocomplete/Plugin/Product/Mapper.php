<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/SearchAutocomplete.
 *
 * OmnyfyCustomzation/SearchAutocomplete is free software: you can redistribute it and/or modify
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

namespace OmnyfyCustomzation\SearchAutocomplete\Plugin\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Mapper
{
    const IS_SHOW_DESCRIPTION = 'searchautocomplete/general/product/show_description';
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function aroundGetDescription(
        \Mirasvit\SearchAutocomplete\Index\Magento\Catalog\Product\Mapper $subject,
        \Closure $proceed,
        Product $product
    )
    {
        if (!$this->scopeConfig->getValue(self::IS_SHOW_DESCRIPTION)) {
            return '';
        }
        $result = $product->getDataUsingMethod('short_description');
        if (!$result) {
            $result = $product->getDataUsingMethod('description');
        }

        return preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($result)));
    }
}
