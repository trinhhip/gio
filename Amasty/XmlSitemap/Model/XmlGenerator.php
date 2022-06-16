<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Model\Sitemap\SourceProvider;
use Amasty\XmlSitemap\Model\Sitemap\UrlProvider;
use Amasty\XmlSitemap\Model\Sitemap\XmlMetaProvider;
use Amasty\XmlSitemap\Model\Writer\Factory as WriterFactory;
use Generator;
use Magento\Framework\Stdlib\DateTime\DateTime;

class XmlGenerator
{
    const XML_HEAD = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;

    const XML_URLSET_START = '<urlset %s>' . PHP_EOL;
    const XML_URLSET_END = '</urlset>';

    const XML_URLSET_DEFAULT_ARG = 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
    const XML_URLSET_IMAGE_ARG = 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
    const XML_URLSET_XHTML_ARG = 'xmlns:xhtml="http://www.w3.org/1999/xhtml"';

    const XML_INDEX_START = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
    const XML_INDEX_END = '</sitemapindex>';

    /**
     * @var WriterFactory
     */
    private $writerFactory;

    /**
     * @var SourceProvider
     */
    private $sourceProvider;

    /**
     * @var XmlMetaProvider
     */
    private $metaProvider;

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        WriterFactory $writerFactory,
        SourceProvider $sourceProvider,
        XmlMetaProvider $metaProvider,
        UrlProvider $urlProvider,
        DateTime $dateTime
    ) {
        $this->writerFactory = $writerFactory;
        $this->sourceProvider = $sourceProvider;
        $this->metaProvider = $metaProvider;
        $this->urlProvider = $urlProvider;
        $this->dateTime = $dateTime;
    }

    public function generate(SitemapInterface $sitemap): void
    {
        $writer = $this->getWriter($sitemap->getWriterConfig());
        $writer->open($sitemap->getFilePath());
        $writer->write($this->getContentGenerator($sitemap), $this->getSitemapParts($sitemap));

        if ($writer->shouldCreateIndexFile()) {
            $writer->writeIndex($this->getIndexGenerator($sitemap, $writer->getFiles()), $this->getIndexParts());
        }
    }

    private function getWriter(array $config): WriterInterface
    {
        return $this->writerFactory->create($config);
    }

    private function getContentGenerator(SitemapInterface $sitemap): Generator
    {
        $excludeUrls = $sitemap->getUrlsToExclude();
        foreach ($this->sourceProvider->getSourcesToGeneration($sitemap) as $entityName => $source) {
            $entityMeta = $this->metaProvider->getMeta($entityName);

            foreach ($source->getData($sitemap) as $data) {
                foreach ($data as $urlTagData) {
                    if (!$this->validate($excludeUrls, $urlTagData)) {
                        continue;
                    }

                    yield $this->getXml($entityMeta, $urlTagData) . PHP_EOL;
                }
            }
        }
    }

    private function validate(array $excludeUrls, array $urlTagData): bool
    {
        $url = $urlTagData['loc'] ?? '';
        if ($url && in_array($url, $excludeUrls)) {
            return false;
        }

        return true;
    }

    private function getIndexGenerator(SitemapInterface $sitemap, array $files): Generator
    {
        foreach ($files as $file) {
            $url = $this->urlProvider->getSitemapUrl($file, $sitemap->getStoreId());

            yield $this->getXml(
                $this->metaProvider->getIndexMeta(),
                [
                    'loc' => $url,
                    'lastmod' =>  $this->dateTime->gmtDate($sitemap->getDateFormat())
                ]
            );
        }
    }

    protected function getSitemapParts(SitemapInterface $sitemap): array
    {
        return [
            WriterInterface::PART_HEADER => $this->getHeader($sitemap),
            WriterInterface::PART_FOOTER => $this->getFooter()
        ];
    }

    protected function getIndexParts(): array
    {
        return [
            WriterInterface::PART_HEADER => $this->getIndexHeader(),
            WriterInterface::PART_FOOTER => $this->getIndexFooter()
        ];
    }

    protected function getHeader(SitemapInterface $sitemap): string
    {
        $headerXml = self::XML_HEAD;
        $urlsetArgs = [self::XML_URLSET_DEFAULT_ARG];

        if ($sitemap->shouldAddImages()) {
            $urlsetArgs[] = self::XML_URLSET_IMAGE_ARG;
        }

        if ($sitemap->shouldAddHreflangs()) {
            $urlsetArgs[] = self::XML_URLSET_XHTML_ARG;
        }
        $headerXml .= sprintf(self::XML_URLSET_START, implode(' ', $urlsetArgs));

        return $headerXml;
    }

    protected function getFooter(): string
    {
        return self::XML_URLSET_END;
    }

    protected function getIndexHeader(): string
    {
        return self::XML_HEAD . self::XML_INDEX_START;
    }

    protected function getIndexFooter(): string
    {
        return self::XML_INDEX_END;
    }

    private function getXml(array $xmlTags, array $data): string
    {
        $xml = '';
        $canAddWrapper = true;

        foreach ($data as $key => $dataItem) {
            if ($key === XmlMetaProvider::ATTRIBUTES) {
                continue;
            }

            if (is_array($dataItem)) {
                if (isset($xmlTags[$key])) {
                    $xml .= $this->getXml($xmlTags[$key], $dataItem);
                } else {
                    // allow to use no-index arrays
                    $xml .= $this->getXml($xmlTags, $dataItem);
                    $canAddWrapper = false;
                }
            } else {
                $xml .= $this->renderTag($xmlTags[$key], (string)$dataItem) . PHP_EOL;
            }
        }
        $wrapper = $xmlTags[XmlMetaProvider::WRAPPER] ?? null;
        $attributes = $data[XmlMetaProvider::ATTRIBUTES] ?? [];
        $isSelfClosing = $xmlTags[XmlMetaProvider::SELF_CLOSING_TAG] ?? false;

        if ($canAddWrapper && $wrapper !== null) {
            $xml = PHP_EOL . $xml;

            if ($isSelfClosing) {
                $xml = $this->renderSelfClosingTag($wrapper, $attributes);
            } else {
                $xml = $this->renderTag($wrapper, $xml, $attributes);
            }
            $xml .= PHP_EOL;
        }

        return $xml;
    }

    private function renderAttributes(array $attributes): string
    {
        $stringAttributes = '';

        foreach ($attributes as $attribute => $attributeValue) {
            $stringAttributes .= sprintf(' %s="%s"', $attribute, $attributeValue);
        }

        return $stringAttributes;
    }

    private function renderTag(string $tag, string $value, array $attributes = []): string
    {
        return sprintf('<%s%s>%s</%s>', $tag, $this->renderAttributes($attributes), $value, $tag);
    }

    private function renderSelfClosingTag(string $tag, array $attributes): string
    {
        return sprintf('<%s%s/>', $tag, $this->renderAttributes($attributes));
    }
}
