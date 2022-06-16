<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Api\SitemapEntity;

/**
 * @api
 */
interface SitemapEntityDataInterface
{
    const ENTITY_CODE = 'entity_code';
    const ENABLED = 'enabled';
    const HREFLANG = 'hreflang';
    const PRIORITY = 'priority';
    const FREQUENCY = 'frequency';
    const ADDITIONAL = 'additional';

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return bool
     */
    public function isAddHreflang(): bool;

    /**
     * @return float
     */
    public function getPriority(): float;

    /**
     * @return string
     */
    public function getFrequency(): string;
}
