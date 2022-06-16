<?php declare(strict_types=1);
/**
 * Copyright © Omnyfy, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Omnyfy\VendorSearch\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;

/**
 * Class Image
 * @package Omnyfy\VendorSearch\Model\Config\Backend
 */
class Image extends File
{
    /**
     * {@inheritdoc}
     */
    protected function _getAllowedExtensions()
    {
        return ['svg', 'jpg', 'jpeg', 'gif', 'png'];
    }
}
