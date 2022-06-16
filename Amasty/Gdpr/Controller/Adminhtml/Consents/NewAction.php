<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Controller\Adminhtml\Consents;

use Amasty\Gdpr\Controller\Adminhtml\AbstractConsents;

class NewAction extends AbstractConsents
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
