<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CustomerAction implements OptionSourceInterface
{
    const ACCEPT = 1;
    const DECLINE = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' =>__('Accept'),
                'value' => self::ACCEPT
            ],
            [
                'label' =>__('Decline'),
                'value' => self::DECLINE
            ]
        ];
    }
}
