<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-20
 * Time: 17:56
 */
namespace Omnyfy\Approval\Controller\Adminhtml\Record;

use Omnyfy\Approval\Controller\Adminhtml\AbstractAction;

class Approve extends AbstractAction
{
    const ADMIN_RESOURCE = 'Omnyfy_Approval::record';

    protected $resourceKey = 'Omnyfy_Approval::record';

    protected $adminTitle = 'Approve Product';

    public function execute()
    {
        $this->_forward('edit', null, null, ['type' => 'approve']);
    }
}
 