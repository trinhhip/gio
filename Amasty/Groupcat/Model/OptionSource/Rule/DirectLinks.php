<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

declare(strict_types=1);

namespace Amasty\Groupcat\Model\OptionSource\Rule;

use Magento\Framework\Data\OptionSourceInterface;

class DirectLinks implements OptionSourceInterface
{
    const DENY = 0;
    const ALLOW = 1;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::DENY, 'label' => __('Deny')],
            ['value' => self::ALLOW, 'label' => __('Allow')]
        ];
    }
}
