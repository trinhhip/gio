<?php
/**
 * Project: Multi Vendor M2.
 * User: jing
 * Date: 9/8/17
 * Time: 8:57 AM
 */
namespace Omnyfy\Vendor\Controller\Adminhtml\Vendor\Subvendor;

class NewAction extends \Omnyfy\Vendor\Controller\Adminhtml\AbstractAction
{
    const ADMIN_RESOURCE = 'Omnyfy_Vendor::vendor_subvendor';

    protected $resourceKey = 'Omnyfy_Vendor::vendor_subvendor';

    protected $adminTitle = 'Subvendors';

    public function execute()
    {
        $this->_forward('edit');
    }
}