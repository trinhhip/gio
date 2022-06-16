<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap;

use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntitySourceInterface;
use Amasty\XmlSitemap\Api\SitemapInterface;
use Magento\Framework\ObjectManagerInterface;

class SourceProvider
{
    /**
     * @var string[]
     */
    private $sources;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        array $sources = []
    ) {
        $this->sources = $sources;
        $this->objectManager = $objectManager;
    }

    public function getSourcesToGeneration(SitemapInterface $sitemap): array
    {
        $sources = [];
        $sourcesClassnames = array_filter($this->sources, function ($sourceName) use ($sitemap) {
            $sitemapEntityData = $sitemap->getEntityData($sourceName);

            return $sitemapEntityData && $sitemapEntityData->isEnabled();
        }, ARRAY_FILTER_USE_KEY);

        foreach ($sourcesClassnames as $sourceCode => $classname) {
            $sources[$sourceCode] = $this->objectManager->get($classname);
        }

        return $sources;
    }

    /**
     * @return SitemapEntitySourceInterface[]
     */
    public function getAllSources(): array
    {
        return array_map(function ($className) {

            return $this->objectManager->get($className);
        }, $this->sources);
    }

    public function getSourcesCodes(): array
    {
        return array_keys($this->sources);
    }
}
