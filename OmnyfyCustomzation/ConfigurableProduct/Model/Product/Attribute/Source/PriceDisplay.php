<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/ConfigurableProduct.
 *
 * OmnyfyCustomzation/ConfigurableProduct is free software: you can redistribute it and/or modify
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

namespace OmnyfyCustomzation\ConfigurableProduct\Model\Product\Attribute\Source;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Registry;

class PriceDisplay extends AbstractSource
{
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var Data
     */
    protected $priceHelper;

    public function __construct(
        Registry $registry,
        Data $priceHelper
    )
    {
        $this->registry = $registry;
        $this->priceHelper = $priceHelper;

    }

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options[] = [
            'value' => 0,
            'label' => __('--- Please chose a product ---')
        ];
        /** @var Product $product */
        $product = $this->registry->registry('current_product');
        if ($product){
            if ($product->getTypeId() == 'configurable') {
                $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($childrenProducts as $childrenProduct) {
                    $this->_options[] = [
                        'value' => $childrenProduct->getId(),
                        'label' => $this->currencyFormat($childrenProduct->getFinalPrice()) . ' - ' . $childrenProduct->getName()
                    ];
                }
            }
        }
        return $this->_options;
    }

    public function currencyFormat($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }
}
