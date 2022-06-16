<?php declare(strict_types=1);
/**
 * Copyright © Omnyfy All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Omnyfy\VendorSearch\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Omnyfy\Vendor\Model\Resource\Vendor\Attribute\CollectionFactory;

/**
 * Class Attribute
 */
class Attribute implements OptionSourceInterface
{
    /**
     * Product attribute collection
     * 
     * @var CollectionFactory
     */
    private $attributes;

    /**
     * Exclude incompatible product attributes from the mapping.
     * @var array
     */
    private $excluded = [
        'quantity_and_stock_status',
        'name',
        'sku',
        'price'
    ];

    /**
     * Product attributes constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->attributes = $collectionFactory;
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = $this->attributes
            ->create()
            ->addVisibleFilter();

        $attributeArray = [];
        $attributeArray[] = [
            'label' => __('---- Default Option ----'),
            'value' => '0',
        ];

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (!in_array($attributeCode, $this->excluded)) {
                $attributeArray[] = [
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $attributeCode,
                ];
            }
        }
        return $attributeArray;
    }
}
