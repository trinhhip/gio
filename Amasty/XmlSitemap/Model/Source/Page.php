<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Source;

use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntitySourceInterface;
use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Model\Sitemap\Hreflang\GenerateCombination;
use Amasty\XmlSitemap\Model\Sitemap\HreflangProvider;
use Generator;
use Magento\Cms\Model\ResourceModel\Page\Collection as PageCollection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Page implements SitemapEntitySourceInterface
{
    const ENTITY_CODE = 'cms';

    const ENTITY_HREFLANG_CODE = 'cms-page';

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var HreflangProvider
     */
    private $hreflangProvider;

    /**
     * @var GenerateCombination
     */
    private $generateCombination;

    public function __construct(
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        DateTime $dateTime,
        Escaper $escaper,
        HreflangProvider $hreflangProvider,
        GenerateCombination $generateCombination
    ) {
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
        $this->escaper = $escaper;
        $this->hreflangProvider = $hreflangProvider;
        $this->generateCombination = $generateCombination;
    }

    public function getData(SitemapInterface $sitemap): Generator
    {
        $sitemapEntityData = $sitemap->getEntityData($this->getEntityCode());

        $collection = $this->getPageCollection();
        $this->applyCollectionFilters($collection, $sitemap);

        if ($sitemapEntityData->isAddHreflang()) {
            $hreflangs = $this->hreflangProvider->getData(
                $sitemap->getStoreId(),
                $this->getEntityHrefLangCode(),
                $collection->getAllIds()
            );
        }

        foreach ($collection as $page) {
            /** @var \Magento\Cms\Model\Page $page */
            $pageUrl = $this->getBaseUrl($sitemap->getStoreId()) . $page->getIdentifier();

            $data = [
                'loc' => $this->escaper->escapeHtml($pageUrl),
                'frequency' => $sitemapEntityData->getFrequency(),
                'priority' => $sitemapEntityData->getPriority()
            ];

            if ($sitemapEntityData->getData('last_modified')) {
                $updateTime = strtotime($page->getUpdateTime());
                $data['last_modified'] = $this->dateTime->date($sitemap->getDateFormat(), $updateTime);
            }

            if ($sitemapEntityData->isAddHreflang() && isset($hreflangs[$page->getId()])) {
                $data['hreflang'] = $hreflangs[$page->getId()];
                $data = $this->generateCombination->execute($data);
            } else {
                $data = [$data];
            }

            yield $data;
        }

        $collection->clear();
    }

    private function getPageCollection(): PageCollection
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('is_active', ['eq' => 1]);

        return $collection;
    }

    private function applyCollectionFilters(PageCollection $collection, SitemapInterface $sitemap): void
    {
        $collection->addStoreFilter($sitemap->getStoreId());
    }

    private function getBaseUrl(int $storeId): string
    {
        if (!$this->baseUrl) {
            $store = $this->storeManager->getStore($storeId);
            $isSecure = $store->isUrlSecure();
            $this->baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_LINK, $isSecure);
            $this->baseUrl = rtrim($this->baseUrl, '/') . '/';
        }

        return $this->baseUrl;
    }

    public function getEntityCode(): string
    {
        return self::ENTITY_CODE;
    }

    public function getEntityHrefLangCode(): string
    {
        return self::ENTITY_HREFLANG_CODE;
    }

    public function getEntityLabel(): string
    {
        return __('Pages')->render();
    }
}
