<?php

namespace Omnyfy\Vendor\Plugin\Amasty\AdminActionLog\Block\Tabs;

use Magento\Backend\App\Action\Context;
use Magento\Framework\AuthorizationInterface;

class Customer
{
    /**
     * @var AuthorizationInterface
     */
    protected $_authorization;

    public function __construct(Context $context)
    {
        $this->_authorization = $context->getAuthorization();
    }

    public function afterCanShowTab(\Amasty\AdminActionsLog\Block\Adminhtml\ActionsLog\Tabs\Customer $subject, $result)
    {
        if(!$this->_authorization->isAllowed('Amasty_AdminActionsLog::actions_log')) {
            return false;
        }
        return $result;
    }
}
