<?php declare(strict_types=1);
/**
 * Copyright © Omnyfy, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Omnyfy\VendorSearch\Model\Config\Source\System;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class MapType
 * @package Omnyfy\VendorSearch\Model\Config\Source\System
 */
class MapType implements ArrayInterface
{
    const TYPE_ROAD_MAP  = 'roadmap';
    const TYPE_TERRAIN   = 'terrain';
    const TYPE_SATELLITE = 'satellite';
    const TYPE_HYBRID    = 'hybrid';

    /**
     * Convert options to array
     * 
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::TYPE_ROAD_MAP  => __('Roadmap'),
            self::TYPE_TERRAIN   => __('Terrain'),
            self::TYPE_SATELLITE => __('Satellite'),
            self::TYPE_HYBRID    => __('Hybrid')
        ];
    }
}
