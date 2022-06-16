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

namespace OmnyfyCustomzation\Vendor\ViewModel\Wishlist;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Omnyfy\Vendor\Model\VendorFactory;
use Omnyfy\Vendor\Model\Resource\Vendor as VendorResource;

class Vendor extends DataObject implements ArgumentInterface
{
    /**
     * @var VendorFactory
     */
    public $vendorFactory;
    /**
     * @var VendorResource
     */
    public $vendorResource;

    /**
     * Vendor constructor.
     * @param VendorFactory $vendorFactory
     */
    public function __construct(
        VendorFactory $vendorFactory,
        VendorResource $vendorResource
    )
    {
        $this->vendorFactory = $vendorFactory;
        $this->vendorResource = $vendorResource;
        parent::__construct();
    }

    public function getVendor($productId)
    {
        $vendorId = $this->vendorResource->getVendorIdByProductId($productId);
        $vendor = $this->vendorFactory->create()->load($vendorId);
        return $vendor->getName();
    }
}