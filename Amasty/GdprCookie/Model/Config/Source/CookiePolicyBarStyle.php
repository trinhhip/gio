<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CookiePolicyBarStyle implements OptionSourceInterface
{
    /**#@+*/
    const CONFIRMATION = 0;

    const CONFIRMATION_MODAL = 1;

    const CONFIRMATION_POPUP = 2;

    /**#@-*/

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::CONFIRMATION => __('Classic'),
            self::CONFIRMATION_MODAL => __('Side Bar'),
            self::CONFIRMATION_POPUP => __('Pop Up')
        ];
    }

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
}
