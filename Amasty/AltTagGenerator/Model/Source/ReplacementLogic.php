<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ReplacementLogic implements OptionSourceInterface
{
    const REPLACE = 0;
    const REPLACE_EMPTY = 1;
    const APPEND = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::REPLACE, 'label' => __('Replace Filled Alt Text')],
            ['value' => self::REPLACE_EMPTY, 'label' => __('Only Replace Empty Alt Text')],
            ['value' => self::APPEND, 'label' => __('Append to Existing Alt Text')]
        ];
    }
}
