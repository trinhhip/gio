<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Controller\Adminhtml;

use Magento\Backend\App\Action as BackendAction;

abstract class AbstractConsents extends BackendAction
{
    const ADMIN_RESOURCE = 'Amasty_Gdpr::consents';
}
