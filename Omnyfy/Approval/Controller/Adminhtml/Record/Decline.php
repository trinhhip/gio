<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-20
 * Time: 17:56
 */
namespace Omnyfy\Approval\Controller\Adminhtml\Record;

use Omnyfy\Approval\Controller\Adminhtml\AbstractAction;

class Decline extends AbstractAction
{
    const ADMIN_RESOURCE = 'Omnyfy_Approval::record';

    protected $resourceKey = 'Omnyfy_Approval::record';

    protected $adminTitle = 'Decline Product';

    public function execute()
    {
        $this->_forward('edit', null, null, ['type' => 'decline']);
    }
}
 