<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/Vendor.
 *
 * OmnyfyCustomzation/Vendor is free software: you can redistribute it and/or modify
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

namespace OmnyfyCustomzation\B2C\ViewModel\Wishlist;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use OmnyfyCustomzation\B2C\Helper\Data as HelperData;

class Retail extends DataObject implements ArgumentInterface
{
    /**
     * @var HelperData
     */
    public $helperData;

    public function __construct(
        HelperData $helperData,
        array $data = []
    )
    {
        $this->helperData = $helperData;
        parent::__construct($data);
    }

    public function isShowBoxCart()
    {
        return !$this->helperData->isRetailBuyer();
    }

    public function isShowRetailPrice($product)
    {
        return $product->getForRetail() && $this->helperData->isRetailBuyer();
    }

    public function getRetailLabel()
    {
        return $this->helperData->getAddToCartLabel();
    }

    public function getRetailPrice($product)
    {
        return $this->helperData->getRetailPrice($product);
    }
}
