<?php declare(strict_types=1);
/**
 * Copyright © Omnyfy, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Omnyfy\VendorSearch\Model\Config\Source\System;

use Magento\Framework\Option\ArrayInterface;
use Omnyfy\Vendor\Model\Resource\Vendor;
use Omnyfy\VendorSearch\Helper\MapSearchData;


class MapSearchDistance implements ArrayInterface
{
    protected $_vendorResource;


    public function __construct(
        Vendor $vendorResource
    ) {
        $this->_vendorResource = $vendorResource;
    }

    /**
     * Convert options to array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        $mapSearchDistanceAtt = $this->_vendorResource->getAttribute(MapSearchData::VENDOR_MAP_SEARCH_DISTANCE);
        if($mapSearchDistanceAtt->usesSource()) {
            $options = $mapSearchDistanceAtt->getSource()->getAllOptions();
        }
        return $options;
    }
}
