<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Omnyfy\Vendor\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateVendorAttributes implements DataPatchInterface
{
    private $collectionFactory;

    public function __construct(
        \Omnyfy\Vendor\Model\Resource\Vendor\Attribute\CollectionFactory $collectionFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function apply()
    {
        $attrNeedChange = [
            'return_policy',
            'payment_policy',
            'marketing_policy',
            'shipping_policy'
        ];

        /**
         * @var \Omnyfy\Vendor\Model\Resource\Vendor\Attribute\Collection $attrCollection
        */
        $attrCollection = $this->collectionFactory->create()->addVisibleFilter()->load();
        foreach ($attrCollection as $attr){
            if (in_array($attr->getAttributeCode(), $attrNeedChange)){
                $attr->setIsUserDefined(1);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
