<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Omnyfy\VendorReview\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;

class AddData implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory

    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }
    /**
     * @inheritdoc
     */
    public function apply()
    {
        $installer = $this->moduleDataSetup;
        //Fill table review/omnyfy_vendor_reviewentity
        $reviewEntityCodes = [
            \Omnyfy\VendorReview\Model\Review::ENTITY_PRODUCT_CODE,
            \Omnyfy\VendorReview\Model\Review::ENTITY_CUSTOMER_CODE,
            \Omnyfy\VendorReview\Model\Review::ENTITY_CATEGORY_CODE,
        ];
        foreach ($reviewEntityCodes as $entityCode) {
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('omnyfy_vendor_review_entity'),
                ['entity_code' => $entityCode]
            );
        }

        //Fill table review/omnyfy_vendor_reviewentity
        $reviewStatuses = [1 => 'Approved', 2 => 'Pending', 3 => 'Not Approved'];

        if (!$this->isAddedReviewStatus($this->moduleDataSetup->getConnection())) {
            foreach ($reviewStatuses as $k => $v) {
                $bind = ['status_id' => $k, 'status_code' => $v];
                $this->moduleDataSetup->getConnection()->insertForce(
                    $this->moduleDataSetup->getTable('omnyfy_vendor_review_status'),
                    $bind
                );
            }
        }

        $data = [
            \Omnyfy\VendorReview\Model\Rating::ENTITY_PRODUCT_CODE => [
                ['vendor_rating_code' => 'Quality', 'position' => 0],
                ['vendor_rating_code' => 'Value', 'position' => 0],
                ['vendor_rating_code' => 'Price', 'position' => 0],
            ],
            \Omnyfy\VendorReview\Model\Rating::ENTITY_PRODUCT_REVIEW_CODE => [],
            \Omnyfy\VendorReview\Model\Rating::ENTITY_REVIEW_CODE => [],
        ];

        foreach ($data as $entityCode => $ratings) {
            //Fill table rating/rating_entity
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('vendor_rating_entity'),
                ['entity_code' => $entityCode]
            );
            $entityId = $this->moduleDataSetup->getConnection()->lastInsertId(
                $this->moduleDataSetup->getTable('vendor_rating_entity')
            );

            foreach ($ratings as $bind) {
                //Fill table rating/rating
                $bind['entity_id'] = $entityId;
                $this->moduleDataSetup->getConnection()->insert(
                    $this->moduleDataSetup->getTable('vendor_rating'),
                    $bind
                );
                //Fill table rating/rating_option
                $ratingId = $this->moduleDataSetup->getConnection()->lastInsertId(
                    $this->moduleDataSetup->getTable('vendor_rating')
                );
                $optionData = [];
                for ($i = 1; $i <= 5; $i++) {
                    $optionData[] = ['vendor_rating_id' => $ratingId, 'code' => (string)$i, 'value' => $i, 'position' => $i];
                }
                $this->moduleDataSetup->getConnection()->insertMultiple(
                    $this->moduleDataSetup->getTable('vendor_rating_option'),
                    $optionData
                );
            }
        }
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * Check omnyfy_vendor_review_status was added data or not
     * This function for migrating 2.2 to 2.4
     * 
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $conn
     * 
     * @return boolean
     */
    private function isAddedReviewStatus($conn)
    {
        $query = $conn->select()->from('omnyfy_vendor_review_status', 'status_id');
        $result = $conn->fetchAll($query);

        return !empty($result) ? true : false;
    }
}
