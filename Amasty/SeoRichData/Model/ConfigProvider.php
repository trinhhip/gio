<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    const STREET_ADDRESS_PATH = 'organization/street';
    const RATING_FORMAT_PATH = 'product/rating_format';
    const PRICE_VALID_DEFAULT_PATH = 'product/price_valid_until';
    const PRICE_VALID_REPLACE_PATH = 'product/replace_price_valid_until';

    /**
     * @var string
     */
    protected $pathPrefix = 'amseorichdata/';

    public function getStreetAddress(): string
    {
        return (string) $this->getValue(self::STREET_ADDRESS_PATH);
    }

    public function getRatingFormat(): int
    {
        return (int) $this->getValue(self::RATING_FORMAT_PATH);
    }

    public function getDefaultPriceValidUntil(): string
    {
        return (string) $this->getValue(self::PRICE_VALID_DEFAULT_PATH);
    }

    public function isReplacePriceValidUntil(): bool
    {
        return $this->isSetFlag(self::PRICE_VALID_REPLACE_PATH);
    }
}
