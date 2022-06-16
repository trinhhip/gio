<?php

/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Tool;

use OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Actions;

/**
 * Admin cms tools edit controller
 */
class Template extends Actions
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'OmnyfyCustomzation_CmsBlog::tool_template';

    /**
     * Form session key
     * @var string
     */
    protected $_formSessionKey = 'cms_tool_template_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey = 'OmnyfyCustomzation_CmsBlog::tool_template';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass = 'OmnyfyCustomzation\CmsBlog\Model\ToolTemplate';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu = 'OmnyfyCustomzation_CmsBlog::tool_template';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField = 'status';

    /**
     * Request id key
     * @var string
     */
    protected $_idKey = 'id';

    protected $_massActionRequestKey = 'selected';


}
