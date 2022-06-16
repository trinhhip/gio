<?php


namespace OmnyfyCustomzation\B2C\Block\Trade;


use Magento\Customer\Model\Context;

class RegisterLink extends \Magento\Customer\Block\Account\RegisterLink
{
    public function getHref()
    {
        return $this->getUrl('buyer/trade');
    }

    protected function _toHtml()
    {
        if (!$this->_registration->isAllowed()
            || $this->httpContext->getValue(Context::CONTEXT_AUTH)
        ) {
            return '';
        }
        return '<li><a ' . $this->getLinkAttributes() . ' >' . __('Trade Account') . '</a></li>';
    }
}