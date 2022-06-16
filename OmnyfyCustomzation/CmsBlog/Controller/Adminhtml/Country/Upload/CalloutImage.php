<?php

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Country\Upload;

use OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Upload\Image\Action;

/**
 * Cms featured image upload controller
 */
class CalloutImage extends Action
{
    /**
     * File key
     *
     * @var string
     */
    protected $_fileKey = 'callout_image';

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('OmnyfyCustomzation_CmsBlog::country');
    }

}
