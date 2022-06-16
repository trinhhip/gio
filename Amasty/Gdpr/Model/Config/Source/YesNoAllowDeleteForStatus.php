<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\Config\Source;

class YesNoAllowDeleteForStatus implements \Magento\Framework\Data\OptionSourceInterface
{
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
            0 => __('Allow'),
            1 => __('Don\'t Allow for Statuses')
        ];
    }
}
