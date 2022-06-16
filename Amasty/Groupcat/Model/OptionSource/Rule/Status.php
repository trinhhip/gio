<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

declare(strict_types=1);

namespace Amasty\Groupcat\Model\OptionSource\Rule;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    const INACTIVE = 0;
    const ACTIVE = 1;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::ACTIVE, 'label' => __('Active')],
            ['value' => self::INACTIVE, 'label' => __('Inactive')]
        ];
    }
}
