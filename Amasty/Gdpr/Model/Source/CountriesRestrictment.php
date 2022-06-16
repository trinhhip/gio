<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CountriesRestrictment implements OptionSourceInterface
{
    const ALL_COUNTRIES = 0;

    const EEA_COUNTRIES = 1;

    const SPECIFIED_COUNTRIES = 2;

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'label' =>__('All Countries'),
                'value' => self::ALL_COUNTRIES
            ],
            [
                'label' =>__('EEA Countries'),
                'value' => self::EEA_COUNTRIES
            ],
            [
                'label' =>__('Specified Countries'),
                'value' => self::SPECIFIED_COUNTRIES
            ]
        ];
    }
}
