<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cms url model
 */
class Url
{
    /**
     * Permalink Types
     */
    const PERMALINK_TYPE_DEFAULT = 'default';
    const PERMALINK_TYPE_SHORT = 'short';

    /**
     * Objects Types
     */
    const CONTROLLER_POST = 'article';
    const CONTROLLER_CATEGORY = 'category';
    const CONTROLLER_INDUSTRY = 'industry';
    const CONTROLLER_ARCHIVE = 'archive';
    const CONTROLLER_AUTHOR = 'author';
    const CONTROLLER_SEARCH = 'search';
    const CONTROLLER_RSS = 'rss';
    const CONTROLLER_TAG = 'tag';
    const CONTROLLER_USER_TYPE = 'user_type';
    const CONTROLLER_TOOL_TEMPLATE = 'tooltemplate';
    const CONTROLLER_COUNTRY = 'country';


    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Initialize dependencies.
     *
     * @param Registry $registry
     * @param UrlInterface $url
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Registry $registry,
        UrlInterface $url,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->_registry = $registry;
        $this->_url = $url;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve controller name by route
     * @param string $route
     * @param boolean $skip
     * @return string || null
     */
    public function getControllerName($route, $skip = true)
    {
        foreach ([
                     self::CONTROLLER_POST,
                     self::CONTROLLER_INDUSTRY,
                     self::CONTROLLER_CATEGORY,
                     self::CONTROLLER_ARCHIVE,
                     self::CONTROLLER_AUTHOR,
                     self::CONTROLLER_TAG,
                     self::CONTROLLER_USER_TYPE,
                     self::CONTROLLER_COUNTRY,
                     self::CONTROLLER_TOOL_TEMPLATE,
                     self::CONTROLLER_SEARCH
                 ] as $controllerName) {
            if ($this->getRoute($controllerName) == $route) {
                return $controllerName;
            }
        }

        return $skip ? $route : null;
    }

    /**
     * Retrieve route name by controller
     * @param string $controllerName
     * @param boolean $skip
     * @return string || null
     */
    public function getRoute($controllerName = null, $skip = true)
    {
        if ($controllerName) {
            $controllerName .= '_';
        }

        if ($route = $this->_getConfig($controllerName . 'route')) {
            return $route;
        } else {
            return $skip ? $controllerName : null;
        }
    }

    /**
     * Retrieve cms base url
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_url->getUrl($this->getRoute());
    }

    /**
     * Retrieve cms page url
     * @param string $identifier
     * @param string $controllerName
     * @return string
     */
    public function getUrl($identifier, $controllerName)
    {
        return $this->_url->getUrl(
            $this->getUrlPath($identifier, $controllerName)
        );
    }

    /**
     * Retrieve cms url path
     * @param string $identifier
     * @param string $controllerName
     * @return string
     */
    public function getUrlPath($identifier, $controllerName)
    {
        if (is_object($identifier)) {
            $identifier = $identifier->getIdentifier();
        }

        switch ($this->getPermalinkType()) {
            case self::PERMALINK_TYPE_DEFAULT :
                return $this->getRoute() . '/' . $this->getRoute($controllerName) . '/' . $identifier;
            case self::PERMALINK_TYPE_SHORT :
                if ($controllerName == self::CONTROLLER_SEARCH
                    || $controllerName == self::CONTROLLER_AUTHOR
                    || $controllerName == self::CONTROLLER_TAG
                    || $controllerName == self::CONTROLLER_USER_TYPE
                    || $controllerName == self::CONTROLLER_COUNTRY
                    || $controllerName == self::CONTROLLER_TOOL_TEMPLATE

                ) {
                    return $this->getRoute() . '/' . $this->getRoute($controllerName) . '/' . $identifier;
                } else {
                    return $this->getRoute() . '/' . $identifier;
                }
        }
    }

    /**
     * Retrieve permalink type
     * @return string
     */
    public function getPermalinkType()
    {
        return $this->_getConfig('type');
    }

    /**
     * Retrieve cms permalink config value
     * @param string $key
     * @return string || null || int
     */
    protected function _getConfig($key)
    {
        return $this->_scopeConfig->getValue(
            'mfcms/permalink/' . $key,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve media url
     * @param string $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->_storeManager->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $file;
    }

}
