<?php

namespace OmnyfyCustomzation\CmsBlog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Admin extends AbstractHelper
{

    /**
     * Path to store config where count of events posts per page is stored
     *
     * @var string
     */
    const XML_PATH_ITEMS_PER_PAGE = 1;
    const XML_PATH = 'mfcms/service_category/';
    protected $storeManager;
    protected $objectManager;

    public function __construct(Context $context, ObjectManagerInterface $objectManager, StoreManagerInterface $storeManager
    )
    {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH . $code, $storeId);
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path, ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
