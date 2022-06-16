<?php


namespace OmnyfyCustomzation\B2C\Controller\Trade;

use OmnyfyCustomzation\B2C\Controller\AbstractRegister;

class CreatePost extends AbstractRegister
{
    public function getCustomerGroupId()
    {
        return $this->helperData->getTradeCustomerGroup();
    }
}
