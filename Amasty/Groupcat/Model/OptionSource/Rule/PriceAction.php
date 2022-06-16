<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

declare(strict_types=1);

namespace Amasty\Groupcat\Model\OptionSource\Rule;

use Magento\Framework\Data\OptionSourceInterface;

class PriceAction implements OptionSourceInterface
{
    const SHOW = 0;
    const HIDE = 1;
    const REPLACE = 2;
    const REPLACE_TO_REQUEST_FORM = 3;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::SHOW, 'label' => __('Show')],
            ['value' => self::HIDE, 'label' => __('Hide')],
            ['value' => self::REPLACE, 'label' => __('Replace')],
            ['value' => self::REPLACE_TO_REQUEST_FORM, 'label' => __('Replace to request form')]
        ];
    }
}
