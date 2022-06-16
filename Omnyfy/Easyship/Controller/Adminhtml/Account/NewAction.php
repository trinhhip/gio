<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\Account;

class NewAction extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_Easyship::easyshipaccount';

    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ){
        parent::__construct($context);
    }

    public function execute()
    {
        return $this->_forward('edit');
    }
}