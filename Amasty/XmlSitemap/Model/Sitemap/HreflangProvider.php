<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap;

use Amasty\XmlSitemap\Model\Sitemap\Hreflang\LanguageCodeProvider;
use Amasty\XmlSitemap\Model\Sitemap\Hreflang\UrlProvider;
use Magento\UrlRewrite\Model\UrlRewrite;

class HreflangProvider
{
    /**
     * @var LanguageCodeProvider
     */
    private $languageCodeProvider;

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * @var string[]
     */
    private $languageCodes;

    public function __construct(
        UrlProvider $urlProvider,
        LanguageCodeProvider $languageCodeProvider
    ) {
        $this->urlProvider = $urlProvider;
        $this->languageCodeProvider = $languageCodeProvider;
    }

    public function getData(int $storeId, string $entityType, array $entityIds): array
    {
        $storeIds = $this->getAffectedStoresIds($storeId);
        $result = [];

        if (empty($storeId)) {
            return $result;
        }
        $urls = $this->urlProvider->getUrls($storeIds, $entityType, $entityIds);

        /** @var UrlRewrite $urlRewrite */
        foreach ($urls as $urlRewrite) {
            $entityId = (int) $urlRewrite->getEntityId();
            $storeId = (int) $urlRewrite->getStoreId();
            $language = $this->getLanguageCode($storeId);

            $result[$entityId][] = [
                XmlMetaProvider::ATTRIBUTES => [
                    'hreflang' => $language,
                    'rel' => 'alternate',
                    'href' => $urlRewrite->getData('url')
                ]
            ];
        }

        return $result;
    }

    private function getLanguageCode(int $storeId): string
    {
        if (!isset($this->languageCodes)) {
            $this->languageCodes = $this->languageCodeProvider->getData($storeId);
        }

        return $this->languageCodes[$storeId];
    }

    private function getAffectedStoresIds(int $storeId): array
    {
        if (!isset($this->languageCodes)) {
            $this->languageCodes = $this->languageCodeProvider->getData($storeId);
        }

        return array_keys($this->languageCodes);
    }
}
