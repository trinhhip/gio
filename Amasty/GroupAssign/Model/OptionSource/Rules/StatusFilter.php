<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Model\OptionSource\Rules;

use Magento\Framework\Option\ArrayInterface;
use Amasty\GroupAssign\Model\Rule;

class StatusFilter implements ArrayInterface
{
    public function toOptionArray()
    {
        $statuses = [
            [
                'value' => Rule::STATUS_DISABLED,
                'label' => __('Disabled')
            ],
            [
                'value' => Rule::STATUS_ENABLED,
                'label' => __('Enabled')
            ]
        ];

        return $statuses;
    }
}
