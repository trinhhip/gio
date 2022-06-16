<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class AbstractRules extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_GroupAssign::rules';
}
