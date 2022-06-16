<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ConsentLinkType implements OptionSourceInterface
{
    const PRIVACY_POLICY = 0;

    const CMS_PAGE = 1;

    /**
     * @return array|void
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('GDPR Privacy policy'),
                'value' => self::PRIVACY_POLICY
            ],
            [
                'label' => __('CMS Page'),
                'value' => self::CMS_PAGE
            ]
        ];
    }
}
