<?php

namespace Omnyfy\Vendor\Plugin\Subvendor;

class RoleSave
{

    protected $title;

    public function afterExecute(\Magento\User\Controller\Adminhtml\User\Role\SaveRole $subject, $result)
    {

        if ($subject->getRequest()->getParam('is_subvendor')) {

        }

        return $result;
    }
}
