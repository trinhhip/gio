<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class NoRecommended implements OptionSourceInterface
{
    const NO = 0;
    const YES = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::YES => __('Yes'),
            self::NO => __('No (Recommended)'),
        ];
    }
}
