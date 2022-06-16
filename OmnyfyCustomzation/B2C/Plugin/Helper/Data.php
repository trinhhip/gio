<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/ShippingNote.
 *
 * OmnyfyCustomzation/ShippingNote is free software: you can redistribute it and/or modify
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

namespace OmnyfyCustomzation\B2C\Plugin\Helper;

use Amasty\HidePrice\Helper\Data as HidePriceHelper;
use OmnyfyCustomzation\B2C\Helper\Data as HelperData;

class Data
{
    /**
     * @var HelperData
     */
    public $helperData;


    public function __construct(
        HelperData $helperData
    )
    {
        $this->helperData = $helperData;
    }

    public function afterGetNewPriceHtmlBox(HidePriceHelper $subject, $result, $product)
    {
        $retailPriceHtml = $this->helperData->getRetailPriceHtml($product);
        return $result . $retailPriceHtml;
    }

    public function afterIsApplied(HidePriceHelper $subject, $result, $product)
    {
        if ($this->helperData->isRetailBuyer()) {
            if (($this->helperData->isProductPage() || $this->helperData->isWishListConfigPage()) && $this->helperData->isProductRetail($product)) {
                return false;
            }
            return true;
        }
        return $result;
    }

//    public function afterGetModuleConfig(HidePriceHelper $subject, $result, $path)
//    {
////        if ($path == 'information/hide_button' && $subject) {
////            return false;
////        }
//        return $result;
//    }
}
