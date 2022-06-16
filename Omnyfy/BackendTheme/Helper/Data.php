<?php

namespace Omnyfy\BackendTheme\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ADMIN_PAGE_TITLE = 'omnyfy_backend/admin_backend/admin_page_title';

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    )
    {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    public function getAdminPageTitle()
    {
        return $this->scopeConfig->getValue(self::ADMIN_PAGE_TITLE);
    }
}
