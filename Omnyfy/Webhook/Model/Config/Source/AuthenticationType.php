<?php
namespace Omnyfy\Webhook\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class AuthenticationType implements \Magento\Framework\Option\ArrayInterface
{
    const TYPE_BASIC = 'basic';

    const TYPE_BEARER = 'bearer';
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_BASIC, 'label' => __('Basic')],
            ['value' => self::TYPE_BEARER, 'label' => __('Bearer Token')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [self::TYPE_BASIC => __('Basic'), self::TYPE_BEARER => __('Bearer Token')];
    }
}
