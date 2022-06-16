<?php

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Tool\Template\Upload;

use OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Upload\Image\Action;

/**
 * Cms featured image upload controller
 */
class UploadTemplate extends Action
{
    /**
     * File key
     *
     * @var string
     */
    protected $_fileKey = 'upload_template';

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('OmnyfyCustomzation_CmsBlog::tool_template');
    }

}
