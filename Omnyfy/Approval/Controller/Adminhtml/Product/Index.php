<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-20
 * Time: 17:51
 */
namespace Omnyfy\Approval\Controller\Adminhtml\Product;

use Omnyfy\Approval\Controller\Adminhtml\AbstractAction;

class Index extends AbstractAction
{
    const ADMIN_RESOURCE = 'Omnyfy_Approval::product';

    protected $resourceKey = 'Omnyfy_Approval::product';

    protected $adminTitle = 'Approve Product';

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();


        $resultPage->getConfig()->getTitle()->prepend(__('Vendor Products Approval'));
        $resultPage->addBreadcrumb(__('Omnyfy'), __('Omnyfy'));
        $resultPage->addBreadcrumb(__('Products Approval'), __('Products Approval'));


        return $resultPage;
    }
}
