<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\VendorReview\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class InitOverallRatingData implements DataPatchInterface
{
    private $moduleDataSetup;

    private $storeRepository;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->storeRepository = $storeRepository;
    }
    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        $this->deleteOverallRatingIfExist($connection);
        $this->insertOverallRating($connection);
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [\Omnyfy\VendorReview\Setup\Patch\Data\AddData::class];
    }

    public function getAliases()
    {
        return [];
    }

    private function insertOverallRating($connection)
    {
        $query = $connection->select()
            ->from($connection->getTableName('vendor_rating_entity'), ['entity_id'])
            ->where('entity_code = ? ', 'vendor');
        $entityId = $connection->fetchOne($query);
        $stores = $this->storeRepository->getList();

        // insert Overall rating
        $vendorRatingTable = $connection->getTableName('vendor_rating');
        $overAllData = [
            'entity_id' => $entityId,
            'vendor_rating_code' => 'Overall',
            'position' => 0
        ];
        $connection->insertOnDuplicate($vendorRatingTable, $overAllData);
        $ratingOverallId = $connection->lastInsertId($vendorRatingTable);

        // insert rating options
        $optionData = [];
        for ($i = 1; $i <= 5; $i++) {
            $optionData[] = ['vendor_rating_id' => $ratingOverallId, 'code' => (string)$i, 'value' => $i, 'position' => $i];
        }
        $connection->insertMultiple(
            $this->moduleDataSetup->getTable('vendor_rating_option'),
            $optionData
        );

        // assign Overall rating to stores
        $ratingStoreTable = $connection->getTableName('vendor_rating_store');
        foreach ($stores as $store) {
            $connection->insertOnDuplicate($ratingStoreTable,
                [
                    'vendor_rating_id' => $ratingOverallId,
                    'store_id' => $store->getId()
                ]
            );
        }
    }

    private function deleteOverallRatingIfExist($connection){
        $vendorRatingTable = $connection->getTableName('vendor_rating');
        $connection->delete($vendorRatingTable, ['vendor_rating_code = ?' => 'Overall']);
    }
}
