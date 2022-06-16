<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\XmlSitemap\Model\OptionSource\CmsRelation;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Store\Model\ScopeInterface;
use Amasty\XmlSitemap\Model\OptionSource\Scope;

class ConfigProvider extends ConfigProviderAbstract
{
    protected $pathPrefix = 'amxmlsitemap/';

    const SEARCH_SUBMISSION = 'search_engines/submission_robots';

    const HREFLANG_SCOPE = 'hreflang/scope';
    const HREFLANG_X_DEFAULT = 'hreflang/x_default';
    const HREFLANG_COUNTRY = 'hreflang/country';
    const HREFLANG_LANGUAGE = 'hreflang/language';
    const HREFLANG_RELATION = 'hreflang/cms_relation';

    public function isHreflangScopeGlobal(): bool
    {
        return (int)$this->getValue(self::HREFLANG_SCOPE) == Scope::SCOPE_GLOBAL;
    }

    public function getHreflangXDefaultStoreId(int $websiteId): int
    {
        return (int)$this->getValue(self::HREFLANG_X_DEFAULT, $websiteId, ScopeInterface::SCOPE_WEBSITES);
    }

    public function getHreflangCountriesByStoreId(int $storeId): ?string
    {
        return $this->getValue(self::HREFLANG_COUNTRY, $storeId, ScopeInterface::SCOPE_STORE);
    }

    public function getHreflangLanguageCode(int $storeId): ?string
    {
        return $this->getValue(self::HREFLANG_LANGUAGE, $storeId, ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultCountryCode(int $storeId): string
    {
        return $this->scopeConfig->getValue(
            DirectoryHelper::XML_PATH_DEFAULT_COUNTRY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getHreflangCmsRelation(): string
    {
        $value = $this->getValue(self::HREFLANG_RELATION);

        if ($value == CmsRelation::UUID) {
            $value = 'amasty_hreflang_uuid';
        }

        return $value;
    }

    public function isSubmissionRobotsEnabled(int $storeId): bool
    {
        return (bool)$this->getValue(self::SEARCH_SUBMISSION, $storeId);
    }
}
