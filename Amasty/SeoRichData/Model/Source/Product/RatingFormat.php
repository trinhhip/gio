<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Source\Product;

use Magento\Framework\Data\OptionSourceInterface;

class RatingFormat implements OptionSourceInterface
{
    const PERCENT = 0;
    const NUMERIC = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::PERCENT => __('Percentage Scale '),
            self::NUMERIC => __('Numeric Scale')
        ];
    }
}
