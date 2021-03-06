<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap\Hreflang;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;

class UrlProvider
{
    /**
     * @var UrlRewriteCollectionFactory
     */
    private $urlCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $baseUrls = [];

    public function __construct(
        UrlRewriteCollectionFactory $urlCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->urlCollectionFactory = $urlCollectionFactory;
        $this->storeManager = $storeManager;
    }

    public function getUrls(array $storeIds, string $entityType, array $entityIds): array
    {
        $collection = $this->urlCollectionFactory->create();

        $collection->addFieldToFilter('is_autogenerated', ['eq' => 1]);
        $collection->addFieldToFilter('entity_type', ['eq' => $entityType]);
        $collection->addFieldToFilter('entity_id', ['in' => $entityIds]);
        $collection->addStoreFilter($storeIds);

        if ($entityType === ProductUrlRewriteGenerator::ENTITY_TYPE) {
            $collection->addFieldToFilter('target_path', ['nlike' => '%category%']);
        }

        $urls = array_map(function (UrlRewrite $rewrite) {
            $url = $this->getStoreBaseUrl((int)$rewrite->getStoreId());
            $url .= ltrim($rewrite['request_path'], '/');
            $rewrite->setData('url', $url);

            return $rewrite;
        }, $collection->getItems());

        return $urls;
    }

    private function getStoreBaseUrl(int $currentStoreId): string
    {
        if (!isset($this->baseUrls[$currentStoreId])) {
            foreach ($this->storeManager->getStores() as $storeId => $store) {
                $this->baseUrls[$storeId] = rtrim($store->getBaseUrl(), '/') . '/';
            }
        }

        return $this->baseUrls[$currentStoreId];
    }
}
