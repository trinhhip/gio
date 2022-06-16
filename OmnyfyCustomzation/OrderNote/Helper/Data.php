<?php

namespace OmnyfyCustomzation\OrderNote\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const ENABLE_MODULE = 'omnyfycustomzation_order_note/general/enable';

    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::ENABLE_MODULE);
    }

    public function getComponentConfig()
    {
        return [
            'settings' => [
                'isEnabled' => (int)$this->isEnabled()
            ]
        ];
    }

}