<?php

namespace OmnyfyCustomzation\CmsBlog\Block\Social;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class AddThis extends Template
{
    /**
     * Retrieve AddThis language code
     *
     * @return boolean
     */
    public function getAddThisLanguage()
    {
        return $this->_scopeConfig->getValue(
            'mfcms/social/add_this_language', ScopeInterface::SCOPE_STORE
        );
    }

    public function toHtml()
    {
        if (!$this->getAddThisEnabled() || !$this->getAddThisPubId()) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * Retrieve AddThis status
     *
     * @return boolean
     */
    public function getAddThisEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            'mfcms/social/add_this_enabled', ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve AddThis publisher id
     *
     * @return boolean
     */
    public function getAddThisPubId()
    {
        return $this->_scopeConfig->getValue(
            'mfcms/social/add_this_pubid', ScopeInterface::SCOPE_STORE
        );
    }
}