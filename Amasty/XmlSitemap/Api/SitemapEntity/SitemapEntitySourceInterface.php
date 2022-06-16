<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Api\SitemapEntity;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Generator;

/**
 * @api
 */
interface SitemapEntitySourceInterface
{
    const LOC = 'loc';
    const FREQUENCY = 'frequency';
    const PRIORITY = 'priority';
    const HREFLANG = 'hreflang';
    const HREF = 'href';

    /**
     * @param SitemapInterface $sitemap
     * @return Generator
     */
    public function getData(SitemapInterface $sitemap): Generator;

    /**
     * @return string
     */
    public function getEntityCode(): string;

    /**
     * @return string
     */
    public function getEntityLabel(): string;
}
