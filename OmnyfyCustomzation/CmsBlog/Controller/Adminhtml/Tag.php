<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml;

/**
 * Admin cms tag edit controller
 */
class Tag extends Actions
{
    /**
     * Form session key
     * @var string
     */
    protected $_formSessionKey = 'cms_tag_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey = 'OmnyfyCustomzation_CmsBlog::article';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass = 'OmnyfyCustomzation\CmsBlog\Model\Tag';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu = 'OmnyfyCustomzation_CmsBlog::article';

    protected $_massActionRequestKey = 'id';
}
