<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-gtm
 * @version   1.0.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\GoogleTagManager\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ResolutionIdentifier implements ArrayInterface
{
    const CHILD_IDENTIFIER  = 'child';
    const PARENT_IDENTIFIER = 'parent';

    public function toOptionArray()
    {
        return [
            ['value' => self::CHILD_IDENTIFIER, 'label' => __('Use child identifier')],
            ['value' => self::PARENT_IDENTIFIER, 'label' => __('Use parent identifier')]
        ];
    }

    public function toArray()
    {
        return [
            self::CHILD_IDENTIFIER  => __('Use child identifier'),
            self::PARENT_IDENTIFIER => __('Use parent identifier')
        ];
    }
}
