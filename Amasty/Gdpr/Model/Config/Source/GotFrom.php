<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class GotFrom implements OptionSourceInterface
{
    const AUTOMATIC = 'automatic';

    const CUSTOMER_REQUEST = 'customer_request';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Automatic Delete'), 'value' => self::AUTOMATIC],
            ['label' => __('Customer\'s Request'), 'value' => self::CUSTOMER_REQUEST]
        ];
    }
}
