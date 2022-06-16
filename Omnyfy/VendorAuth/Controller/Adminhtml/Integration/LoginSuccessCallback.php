<?php
/**
 *
 * Copyright © Omnyfy, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\VendorAuth\Controller\Adminhtml\Integration;

class LoginSuccessCallback extends \Magento\Integration\Controller\Adminhtml\Integration
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorAuth::system_integrations';

    /**
     * Close window after callback has succeeded
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setBody('<script>setTimeout("self.close()",1000);</script>');
    }
}
