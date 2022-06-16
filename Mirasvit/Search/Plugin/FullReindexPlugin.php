<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.22
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Plugin;

use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Model\ConfigProvider;
use Mirasvit\Search\Repository\IndexRepository;

/**
 * @see \Magento\CatalogSearch\Model\Indexer\Fulltext::executeFull()
 */
class FullReindexPlugin
{
    private $indexRepository;

    private $configProvider;

    public function __construct(
        IndexRepository $indexRepository,
        ConfigProvider $configProvider
    ) {
        $this->indexRepository = $indexRepository;
        $this->configProvider  = $configProvider;
    }

    /**
     * @param mixed $scope
     */
    public function aroundExecuteFull(object $fulltext, \Closure $proceed, $scope = null): ?array
    {
        $result = $proceed($scope);

        foreach ($this->indexRepository->getCollection() as $index) {
            if ($index->getIsActive()) {
                if ($index->getIdentifier() == 'catalogsearch_fulltext') {
                    $index->setStatus(IndexInterface::STATUS_READY);
                    $this->indexRepository->save($index);
                } else {
                    $this->indexRepository->getInstance($index)->reindexAll();
                }
            }
        }

        return $result;
    }
}
