<?php
namespace Omnyfy\Catalog\Model\Storage;

use Magento\Framework\DB\Select;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteData;

class DbStorage extends \Magento\UrlRewrite\Model\Storage\DbStorage
{
    /**
     * Delete old url rewrites
     *
     * @param UrlRewrite[] $urls
     * @return void
     */
    private function deleteOldUrls(array $urls)
    {
        $oldUrlsSelect = $this->connection->select();
        $oldUrlsSelect->from(
            $this->resource->getTableName(self::TABLE_NAME)
        );

        $uniqueEntities = $this->prepareUniqueEntities($urls);
        foreach ($uniqueEntities as $storeId => $entityTypes) {
            foreach ($entityTypes as $entityType => $entities) {
                $oldUrlsSelect->orWhere(
                    $this->connection->quoteIdentifier(
                        UrlRewrite::STORE_ID
                    ) . ' = ' . $this->connection->quote($storeId, 'INTEGER') .
                    ' AND ' . $this->connection->quoteIdentifier(
                        UrlRewrite::ENTITY_ID
                    ) . ' IN (' . $this->connection->quote($entities, 'INTEGER') . ')' .
                    ' AND ' . $this->connection->quoteIdentifier(
                        UrlRewrite::ENTITY_TYPE
                    ) . ' = ' . $this->connection->quote($entityType)
                );
            }
        }

        // prevent query locking in a case when nothing to delete
        $checkOldUrlsSelect = clone $oldUrlsSelect;
        $checkOldUrlsSelect->reset(Select::COLUMNS);
        $checkOldUrlsSelect->columns('count(*)');
        $hasOldUrls = (bool)$this->connection->fetchOne($checkOldUrlsSelect);

        if ($hasOldUrls) {
            $this->connection->query(
                $oldUrlsSelect->deleteFromSelect(
                    $this->resource->getTableName(self::TABLE_NAME)
                )
            );
        }
    }

    /**
     * Prepare array with unique entities
     *
     * @param UrlRewrite[] $urls
     * @return array
     */
    private function prepareUniqueEntities(array $urls): array
    {
        $uniqueEntities = [];
        /** @var UrlRewrite $url */
        foreach ($urls as $url) {
            $entityIds = (!empty($uniqueEntities[$url->getStoreId()][$url->getEntityType()])) ?
                $uniqueEntities[$url->getStoreId()][$url->getEntityType()] : [];

            if (!\in_array($url->getEntityId(), $entityIds)) {
                $entityIds[] = $url->getEntityId();
            }
            $uniqueEntities[$url->getStoreId()][$url->getEntityType()] = $entityIds;
        }
        return $uniqueEntities;
    }

    /**
     * @inheritDoc
     */
    protected function doReplace(array $urls) : array
    {
        $this->deleteOldUrls($urls);

        $data = [];
        $urlConflicted = [];
        foreach ($urls as $url) {
            $urlFound = $this->doFindOneByData(
                [
                    UrlRewriteData::REQUEST_PATH => $url->getRequestPath(),
                    UrlRewriteData::STORE_ID => $url->getStoreId(),
                ]
            );
            if (isset($urlFound[UrlRewriteData::URL_REWRITE_ID])) {
                $urlConflicted[$urlFound[UrlRewriteData::URL_REWRITE_ID]] = $url->toArray();
                continue;
            } else {
                $data[] = $url->toArray();
            }
        }

        try {
            $this->insertMultiple($data);
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $urlConflicted */
            $urlConflicted = [];
            foreach ($urls as $url) {
                $urlFound = $this->doFindOneByData(
                    [
                        UrlRewriteData::REQUEST_PATH => $url->getRequestPath(),
                        UrlRewriteData::STORE_ID => $url->getStoreId(),
                    ]
                );
                if (isset($urlFound[UrlRewriteData::URL_REWRITE_ID])) {
                    $urlConflicted[$urlFound[UrlRewriteData::URL_REWRITE_ID]] = $url->toArray();
                }
            }
            if ($urlConflicted) {
                throw new \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException(
                    __('URL key for specified store already exists.'),
                    $e,
                    $e->getCode(),
                    $urlConflicted
                );
            } else {
                throw $e->getPrevious() ?: $e;
            }
        }

        return $urls;
    }

}
