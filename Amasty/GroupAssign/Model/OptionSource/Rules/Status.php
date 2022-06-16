<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Model\OptionSource\Rules;

use Amasty\GroupAssign\Model\Rule;
use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    public function toOptionArray()
    {
        $statuses = [
            [
                'value' => Rule::STATUS_DISABLED, 'label' => '<span class="grid-severity-critical">'
                . htmlspecialchars(__("Disabled"))
                . '</span>'
            ],
            [
                'value' => Rule::STATUS_ENABLED, 'label' => '<span class="grid-severity-notice">'
                . htmlspecialchars(__("Enabled"))
                . '</span>'
            ]
        ];

        return $statuses;
    }
}
