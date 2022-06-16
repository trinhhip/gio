<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Lazy load options
 */
class LazyLoad implements ArrayInterface
{
    const DISABLED = 0;
    const ENABLED_WITH_AUTO_TRIGER = 1;
    const ENABLED_WITHOUT_AUTO_TRIGER = 2;

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DISABLED, 'label' => __('No')],
            ['value' => self::ENABLED_WITH_AUTO_TRIGER, 'label' => __('Yes (With auto trigger)')],
            ['value' => self::ENABLED_WITHOUT_AUTO_TRIGER, 'label' => __('Yes (Without auto trigger)')],
        ];
    }
}
