<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-gtm
 * @version   1.0.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\GoogleTagManager\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface as DefaultScope;
use Magento\Framework\Model\Context;
use Magento\Store\Model\ScopeInterface;
use Mirasvit\Core\Service\SerializeService;

class Config
{
    private $scopeConfig;

    private $context;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->context     = $context;
    }

    /**
     * @param null|DefaultScope $store
     * @return bool
     */
    public function getGeneralIsEnable($store = null)
    {
        return $this->scopeConfig->getValue(
            'mst_gtm/general/enabled',
            ScopeInterface::SCOPE_STORE,
            $store
        ) == 1;
    }

    /**
     * @param null|DefaultScope $store
     * @return string
     */
    public function getGeneralRegularCode($store = null)
    {
        return $this->scopeConfig->getValue(
            'mst_gtm/general/gtm_code_regular',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|DefaultScope $store
     * @return string
     */
    public function getGeneralNoScriptCode($store = null)
    {
        return $this->scopeConfig->getValue(
            'mst_gtm/general/gtm_code_noscript',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|DefaultScope $store
     * @return string
     */
    public function getMappingProductIdentifier($store = null)
    {
        return $this->scopeConfig->getValue(
            'mst_gtm/attribute_mapping/product_identifier',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|DefaultScope $store
     * @return string
     */
    public function getMappingBrandIdentifier($store = null)
    {
        return $this->scopeConfig->getValue(
            'mst_gtm/attribute_mapping/brand_attribute',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|DefaultScope $store
     * @return bool
     */
    public function isMappingTrackVariants($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            'mst_gtm/attribute_mapping/track_variants',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|DefaultScope $store
     * @return array
     */
    public function getMappingCustomData($store = null)
    {
        $data = (string)$this->scopeConfig->getValue(
            'mst_gtm/attribute_mapping/custom_data',
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return (array)SerializeService::decode($data);
    }

    /**
     * @param null|DefaultScope $store
     * @return string
     */
    public function getMappingResolutionIdentifier($store = null)
    {
        return $this->scopeConfig->getValue(
            'mst_gtm/attribute_mapping/resolution_identifier',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|DefaultScope $store
     * @return string
     */
    public function getMappingTrackCustomerGroup($store = null)
    {
        return $this->scopeConfig->getValue(
            'mst_gtm/customer_mapping/track_customer_group',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
