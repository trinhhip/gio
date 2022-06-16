<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use OmnyfyCustomzation\CmsBlog\Model\Url;

/**
 * Used in creating options for permalink config value selection
 */
class PermalinkType implements ArrayInterface
{
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
            ['value' => Url::PERMALINK_TYPE_DEFAULT, 'label' => __('Default: mystore.com/{cms_route}/{article_route}/article-title/')],
            ['value' => Url::PERMALINK_TYPE_SHORT, 'label' => __('Short: mystore.com/{cms_route}/article-title/')],
        ];
    }
}
