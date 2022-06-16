<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model;

use Magento\AdminNotification\Model\Feed;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

/**
 * Cms admin notification feed model
 */
class AdminNotificationFeed extends Feed
{
    /**
     * @var Session
     */
    protected $_backendAuthSession;

    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ConfigInterface $backendConfig
     * @param InboxFactory $inboxFactory
     * @param Session $backendAuthSession
     * @param ModuleListInterface $moduleList
     * @param Manager $moduleManager ,
     * @param CurlFactory $curlFactory
     * @param DeploymentConfig $deploymentConfig
     * @param ProductMetadataInterface $productMetadata
     * @param UrlInterface $urlBuilder
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ConfigInterface $backendConfig,
        InboxFactory $inboxFactory,
        Session $backendAuthSession,
        ModuleListInterface $moduleList,
        Manager $moduleManager,
        CurlFactory $curlFactory,
        DeploymentConfig $deploymentConfig,
        ProductMetadataInterface $productMetadata,
        UrlInterface $urlBuilder,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $backendConfig, $inboxFactory, $curlFactory, $deploymentConfig, $productMetadata, $urlBuilder, $resource, $resourceCollection, $data);
        $this->_backendAuthSession = $backendAuthSession;
        $this->_moduleList = $moduleList;
        $this->_moduleManager = $moduleManager;
    }

    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = 'http://mage' . 'fan'
                . '.c' . 'om/community/notifications' . '/' . 'feed/';
        }

        $urlInfo = parse_url($this->urlBuilder->getBaseUrl());
        $domain = isset($urlInfo['host']) ? $urlInfo['host'] : '';

        $url = $this->_feedUrl . 'domain/' . urlencode($domain);

        $modulesParams = [];
        foreach ($this->getOmnyfyModules() as $key => $module) {
            $key = str_replace('Omnyfy_', '', $key);
            $modulesParams[] = $key . ',' . $module['setup_version'];
        }

        if (count($modulesParams)) {
            $url .= '/modules/' . base64_encode(implode(';', $modulesParams));
        }

        return $url;
    }

    /**
     * Get Omnyfy Modules Info
     *
     * @return $this
     */
    protected function getOmnyfyModules()
    {
        $modules = [];
        foreach ($this->_moduleList->getAll() as $moduleName => $module) {
            if (strpos($moduleName, 'Omnyfy_') !== false && $this->_moduleManager->isEnabled($moduleName)) {
                $modules[$moduleName] = $module;
            }
        }

        return $modules;
    }

    /**
     * Check feed for modification
     *
     * @return $this
     */
    public function checkUpdate()
    {
        $session = $this->_backendAuthSession;
        $time = time();
        $frequency = $this->getFrequency();
        if (($frequency + $session->getMfNoticeLastUpdate() > $time)
            || ($frequency + $this->getLastUpdate() > $time)
        ) {
            return $this;
        }

        $session->setMfNoticeLastUpdate($time);
        return parent::checkUpdate();
    }

    /**
     * Retrieve Update Frequency
     *
     * @return int
     */
    public function getFrequency()
    {
        return 86400;
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->_cacheManager->load('omnyfy_admin_notifications_lastcheck');
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), 'omnyfy_admin_notifications_lastcheck');
        return $this;
    }

}
