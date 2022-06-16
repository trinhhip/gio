<?php

namespace Omnyfy\VendorSearch\Model\VendorSearch;

use Omnyfy\VendorSearch\Model\VendorSearch\Toolbar;
use Omnyfy\VendorSearch\Model\VendorSearch\Session as VendorSession;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ToolbarMemorizer
{
    /**
     * XML PATH to enable/disable saving toolbar parameters to session
     */
    const XML_PATH_CATALOG_REMEMBER_PAGINATION = 'catalog/frontend/remember_pagination';

    /**
     * @var VendorSession
     */
    private $vendorSession;

    /**
     * @var Toolbar
     */
    private $toolbarModel;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string|bool
     */
    private $order;

    /**
     * @var string|bool
     */
    private $direction;

    /**
     * @var string|bool
     */
    private $mode;

    /**
     * @var string|bool
     */
    private $limit;

    /**
     * @var bool
     */
    private $isMemorizingAllowed;

    /**
     * @param Toolbar $toolbarModel
     * @param VendorSession $vendorSession
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Toolbar $toolbarModel,
        VendorSession $vendorSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->toolbarModel = $toolbarModel;
        $this->vendorSession = $vendorSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get sort order
     *
     * @return string|bool
     */
    public function getOrder()
    {
        if ($this->order === null) {
            $this->order = $this->toolbarModel->getOrder() ??
                ($this->isMemorizingAllowed() ? $this->vendorSession->getData(Toolbar::ORDER_PARAM_NAME) : null);
        }
        return $this->order;
    }

    /**
     * Get sort direction
     *
     * @return string|bool
     */
    public function getDirection()
    {
        if ($this->direction === null) {
            $this->direction = $this->toolbarModel->getDirection() ??
                ($this->isMemorizingAllowed() ? $this->vendorSession->getData(Toolbar::DIRECTION_PARAM_NAME) : null);
        }
        return $this->direction;
    }

    /**
     * Get sort mode
     *
     * @return string|bool
     */
    public function getMode()
    {
        if ($this->mode === null) {
            $this->mode = $this->toolbarModel->getMode() ??
                ($this->isMemorizingAllowed() ? $this->vendorSession->getData(Toolbar::MODE_PARAM_NAME) : null);
        }
        return $this->mode;
    }

    /**
     * Get products per page limit
     *
     * @return string|bool
     */
    public function getLimit()
    {
        if ($this->limit === null) {
            $this->limit = $this->toolbarModel->getLimit() ??
                ($this->isMemorizingAllowed() ? $this->vendorSession->getData(Toolbar::LIMIT_PARAM_NAME) : null);
        }
        return $this->limit;
    }

    /**
     * Method to save all vendor search parameters in vendor session
     *
     * @return void
     */
    public function memorizeParams()
    {
        if (!$this->vendorSession->getParamsMemorizeDisabled() && $this->isMemorizingAllowed()) {
            $this->memorizeParam(Toolbar::ORDER_PARAM_NAME, $this->getOrder())
                ->memorizeParam(Toolbar::DIRECTION_PARAM_NAME, $this->getDirection())
                ->memorizeParam(Toolbar::MODE_PARAM_NAME, $this->getMode())
                ->memorizeParam(Toolbar::LIMIT_PARAM_NAME, $this->getLimit());
        }
    }

    /**
     * Check configuration for enabled/disabled toolbar memorizing
     *
     * @return bool
     */
    public function isMemorizingAllowed()
    {
        if ($this->isMemorizingAllowed === null) {
//            $this->isMemorizingAllowed = $this->scopeConfig->isSetFlag(self::XML_PATH_CATALOG_REMEMBER_PAGINATION);
            $this->isMemorizingAllowed = true;
        }
        return $this->isMemorizingAllowed;
    }

    /**
     * Memorize parameter value for session
     *
     * @param string $param parameter name
     * @param mixed $value parameter value
     * @return \Omnyfy\VendorSearch\Model\VendorSearch\ToolbarMemorizer
     */
    private function memorizeParam($param, $value)
    {
        if ($value && $this->vendorSession->getData($param) != $value) {
            $this->vendorSession->setData($param, $value);
        }
        return $this;
    }
}