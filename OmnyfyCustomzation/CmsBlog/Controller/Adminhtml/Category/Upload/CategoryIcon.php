<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Category\Upload;

use OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Upload\Image\Action;

/**
 * Cms featured image upload controller
 */
class CategoryIcon extends Action
{
    /**
     * File key
     *
     * @var string
     */
    protected $_fileKey = 'category_icon';

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('OmnyfyCustomzation_CmsBlog::category');
    }

}
