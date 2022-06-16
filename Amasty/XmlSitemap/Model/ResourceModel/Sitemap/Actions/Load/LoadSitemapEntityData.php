<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\ResourceModel\Sitemap\Actions\Load;

use Amasty\Base\Model\Serializer;
use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntityDataInterface;
use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap as SitemapResource;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap\Actions\AdditionalActionInterface;
use Amasty\XmlSitemap\Model\Sitemap;
use Amasty\XmlSitemap\Model\Sitemap\SitemapEntityDataFactory;
use Magento\Framework\App\ResourceConnection;

class LoadSitemapEntityData implements AdditionalActionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SitemapEntityDataFactory
     */
    private $modelFactory;

    /**
     * @var Serializer
     */
    private $jsonSerializer;

    public function __construct(
        ResourceConnection $resourceConnection,
        SitemapEntityDataFactory $modelFactory,
        Serializer $jsonSerializer
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->modelFactory = $modelFactory;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute(array $sitemapArray): void
    {
        $ids = array_keys($sitemapArray);
        $entitiesData = $this->getEntitiesDataByIds($ids);
        $entitiesData = $this->prepareEntitiesData($entitiesData);

        foreach ($sitemapArray as $sitemapId => $sitemap) {
            if (isset($entitiesData[$sitemapId])) {
                $this->addEntitiesToSitemap($sitemap, $entitiesData[$sitemapId]);
            }
        }
    }

    private function prepareEntitiesData(array $entitiesData): array
    {
        $data = [];

        foreach ($entitiesData as $entityData) {
            $entityData = $this->unserializeAdditionalData($entityData);
            $sitemapId = $entityData[SitemapInterface::SITEMAP_ID];
            $entityCode = $entityData[SitemapEntityDataInterface::ENTITY_CODE];

            $entityDataModel = $this->modelFactory->create();
            $entityDataModel->addData($entityData);

            $data[$sitemapId][$entityCode] = $entityDataModel;
        }

        return $data;
    }

    private function getEntitiesDataByIds(array $ids): array
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName(SitemapResource::ENTITY_DATA_TABLE_NAME);

        $select = $connection->select();
        $select->from($table);
        $condition = $connection->prepareSqlCondition(SitemapInterface::SITEMAP_ID, ['in' => $ids]);
        $select->where($condition);

        return $connection->fetchAll($select);
    }

    private function addEntitiesToSitemap(Sitemap $sitemap, array $data): void
    {
        $sitemap->setData(SitemapInterface::ENTITIES, $data);
        $sitemap->setIsEntitiesDataLoaded(true);
    }

    private function unserializeAdditionalData(array $data): array
    {
        $unserializedData = $this->jsonSerializer->unserialize($data[SitemapEntityDataInterface::ADDITIONAL]);
        if ($unserializedData) {
            $data = array_merge($data, $unserializedData);
        }

        unset($data[SitemapEntityDataInterface::ADDITIONAL]);

        return $data;
    }
}
