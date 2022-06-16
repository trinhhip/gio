<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\VendorSearch\Block\Search;

use Magento\Framework\View\Element\Template\Context;
use Omnyfy\VendorSearch\Helper\Data;
use Omnyfy\VendorSearch\Model\VendorSearch\Toolbar as ToolbarModel;
use Omnyfy\VendorSearch\Model\VendorSearch\ToolbarMemorizer;
use Magento\Framework\App\ObjectManager;

class Toolbar extends \Magento\Framework\View\Element\Template
{
    /**
     * Vendors collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_collection = null;

    /**
     * List of available order fields
     *
     * @var array
     */
    protected $_availableOrder = null;

    /**
     * List of available view types
     *
     * @var array
     */
    protected $_availableMode = [];

    /**
     * Is enable View switcher
     *
     * @var bool
     */
    protected $_enableViewSwitcher = true;

    /**
     * Is Expanded
     *
     * @var bool
     */
    protected $_isExpanded = true;

    /**
     * Default Order field
     *
     * @var string
     */
    protected $_orderField = null;

    /**
     * Default direction
     *
     * @var string
     */
    protected $_direction = Data::DEFAULT_SORT_DIRECTION;

    /**
     * Default View mode
     *
     * @var string
     */
    protected $_viewMode = null;

    /**
     * @var bool $_paramsMemorizeAllowed
     *
     */
    protected $_paramsMemorizeAllowed = true;

    /**
     * @var string
     */
    protected $_template = 'Omnyfy_VendorSearch::search/toolbar.phtml';

    /**
     * Catalog config
     *
     * @var \Omnyfy\Vendor\Model\Config
     */
    protected $_vendorConfig;

    /**
     * Catalog session
     *
     * @var \Omnyfy\VendorSearch\Model\Session
     *
     */
    protected $_vendorSession;

    /**
     * @var ToolbarModelToolbarModel
     */
    protected $_toolbarModel;

    /**
     * @var ToolbarMemorizer
     */
    private $toolbarMemorizer;

    /**
     * @var Data
     */
    protected $_vendorListHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @param Context $context
     * @param \Omnyfy\VendorSearch\Model\Session $vendorSession
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param ToolbarModel $toolbarModel
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param Data $vendorListHelper
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     * @param ToolbarMemorizer|null $toolbarMemorizer
     * @param \Magento\Framework\App\Http\Context|null $httpContext
     * @param \Magento\Framework\Data\Form\FormKey|null $formKey
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        \Omnyfy\VendorSearch\Model\VendorSearch\Session $vendorSession,
        \Magento\Catalog\Model\Config $catalogConfig,
        ToolbarModel $toolbarModel,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        Data $vendorListHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        array $data = [],
        ToolbarMemorizer $toolbarMemorizer = null,
        \Magento\Framework\App\Http\Context $httpContext = null,
        \Magento\Framework\Data\Form\FormKey $formKey = null
    ) {
        $this->_vendorSession = $vendorSession;
        $this->_catalogConfig = $catalogConfig;
        $this->_toolbarModel = $toolbarModel;
        $this->urlEncoder = $urlEncoder;
        $this->_vendorListHelper = $vendorListHelper;
        $this->_postDataHelper = $postDataHelper;
        $this->toolbarMemorizer = $toolbarMemorizer ?: ObjectManager::getInstance()->get(
            ToolbarMemorizer::class
        );
        $this->httpContext = $httpContext ?: ObjectManager::getInstance()->get(
            \Magento\Framework\App\Http\Context::class
        );
        $this->formKey = $formKey ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Data\Form\FormKey::class
        );
        parent::__construct($context, $data);
    }

    /**
     * Disable list state params memorizing
     *
     * @return $this
     * @deprecated 103.0.1
     */
    public function disableParamsMemorizing()
    {
        $this->_paramsMemorizeAllowed = false;
        return $this;
    }

    /**
     * Memorize parameter value for session
     *
     * @param string $param parameter name
     * @param mixed $value parameter value
     * @return $this
     * @deprecated 103.0.1
     */
    protected function _memorizeParam($param, $value)
    {
        if ($this->_paramsMemorizeAllowed && !$this->_vendorSession->getParamsMemorizeDisabled()) {
            $this->_vendorSession->setData($param, $value);
        }
        return $this;
    }

    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            if (($this->getCurrentOrder()) == 'position') {
                $this->_collection->addAttributeToSort(
                    $this->getCurrentOrder(),
                    $this->getCurrentDirection()
                );
            } else {
                $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
            }
        }
        return $this;
    }

    /**
     * Return vendors collection instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_toolbarModel->getCurrentPage();
    }

    /**
     * Get grid Vendors sort order field
     *
     * @return string
     */
    public function getCurrentOrder()
    {
        $order = $this->_getData('_current_grid_order');
        if ($order) {
            return $order;
        }

        $orders = $this->getAvailableOrders();
        $defaultOrder = $this->getOrderField();

        if (!isset($orders[$defaultOrder])) {
            $keys = array_keys($orders);
            $defaultOrder = $keys[0];
        }

        $order = $this->toolbarMemorizer->getOrder();
        if (!$order || !isset($orders[$order])) {
            $order = $defaultOrder;
        }

        if ($this->toolbarMemorizer->isMemorizingAllowed()) {
            $this->httpContext->setValue(ToolbarModel::ORDER_PARAM_NAME, $order, $defaultOrder);
        }

        $this->setData('_current_grid_order', $order);
        return $order;
    }

    /**
     * Retrieve current direction
     *
     * @return string
     */
    public function getCurrentDirection()
    {
        $dir = $this->_getData('_current_grid_direction');
        if ($dir) {
            return $dir;
        }

        $directions = ['asc', 'desc'];
        $dir = strtolower($this->toolbarMemorizer->getDirection());
        if (!$dir || !in_array($dir, $directions)) {
            $dir = $this->_direction;
        }

        if ($this->toolbarMemorizer->isMemorizingAllowed()) {
            $this->httpContext->setValue(ToolbarModel::DIRECTION_PARAM_NAME, $dir, $this->_direction);
        }

        $this->setData('_current_grid_direction', $dir);
        return $dir;
    }

    /**
     * Set default Order field
     *
     * @param string $field
     * @return $this
     */
    public function setDefaultOrder($field)
    {
        $this->loadAvailableOrders();
        if (isset($this->_availableOrder[$field])) {
            $this->_orderField = $field;
        }
        return $this;
    }

    /**
     * Set default sort direction
     *
     * @param string $dir
     * @return $this
     */
    public function setDefaultDirection($dir)
    {
        if (in_array(strtolower($dir), ['asc', 'desc'])) {
            $this->_direction = strtolower($dir);
        }
        return $this;
    }

    /**
     * Retrieve available Order fields list
     *
     * @return array
     */
    public function getAvailableOrders()
    {
        $this->loadAvailableOrders();
        return $this->_availableOrder;
    }

    /**
     * Set Available order fields list
     *
     * @param array $orders
     * @return $this
     */
    public function setAvailableOrders($orders)
    {
        $this->_availableOrder = $orders;
        return $this;
    }

    /**
     * Add order to available orders
     *
     * @param string $order
     * @param string $value
     * @return \Omnyfy\VenndorSearch\Block\Search\Toolbar
     */
    public function addOrderToAvailableOrders($order, $value)
    {
        $this->loadAvailableOrders();
        $this->_availableOrder[$order] = $value;
        return $this;
    }

    /**
     * Remove order from available orders if exists
     *
     * @param string $order
     * @return $this
     */
    public function removeOrderFromAvailableOrders($order)
    {
        $this->loadAvailableOrders();
        if (isset($this->_availableOrder[$order])) {
            unset($this->_availableOrder[$order]);
        }
        return $this;
    }

    /**
     * Compare defined order field with current order field
     *
     * @param string $order
     * @return bool
     */
    public function isOrderCurrent($order)
    {
        return $order == $this->getCurrentOrder();
    }

    /**
     * Return current URL with rewrites and additional parameters
     *
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = false;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }

    /**
     * Get pager encoded url.
     *
     * @param array $params
     * @return string
     */
    public function getPagerEncodedUrl($params = [])
    {
        return $this->urlEncoder->encode($this->getPagerUrl($params));
    }

    /**
     * Retrieve current View mode
     *
     * @return string
     */
    public function getCurrentMode()
    {
        $mode = $this->_getData('_current_grid_mode');
        if ($mode) {
            return $mode;
        }
        $defaultMode = $this->_vendorListHelper->getDefaultViewMode($this->getModes());

        $mode = $this->toolbarMemorizer->getMode();
        if (!$mode || !isset($this->_availableMode[$mode])) {
            $mode = $defaultMode;
        }

        if ($this->toolbarMemorizer->isMemorizingAllowed()) {
            $this->httpContext->setValue(ToolbarModel::MODE_PARAM_NAME, $mode, $defaultMode);
        }

        $this->setData('_current_grid_mode', $mode);
        return $mode;
    }

    /**
     * Compare defined view mode with current active mode
     *
     * @param string $mode
     * @return bool
     */
    public function isModeActive($mode)
    {
        return $this->getCurrentMode() == $mode;
    }

    /**
     * Retrieve available view modes
     *
     * @return array
     */
    public function getModes()
    {
        if ($this->_availableMode === []) {
            $this->_availableMode = $this->_vendorListHelper->getAvailableViewMode();
        }
        return $this->_availableMode;
    }

    /**
     * Set available view modes list
     *
     * @param array $modes
     * @return $this
     */
    public function setModes($modes)
    {
        $this->getModes();
        if (!isset($this->_availableMode)) {
            $this->_availableMode = $modes;
        }
        return $this;
    }

    /**
     * Disable view switcher
     *
     * @return \Omnyfy\VenndorSearch\Block\Search\Toolbar
     */
    public function disableViewSwitcher()
    {
        $this->_enableViewSwitcher = false;
        return $this;
    }

    /**
     * Enable view switcher
     *
     * @return \Omnyfy\VenndorSearch\Block\Search\Toolbar
     */
    public function enableViewSwitcher()
    {
        $this->_enableViewSwitcher = true;
        return $this;
    }

    /**
     * Is a enabled view switcher
     *
     * @return bool
     */
    public function isEnabledViewSwitcher()
    {
        return $this->_enableViewSwitcher;
    }

    /**
     * Disable Expanded
     *
     * @return \Omnyfy\VenndorSearch\Block\Search\Toolbar
     */
    public function disableExpanded()
    {
        $this->_isExpanded = false;
        return $this;
    }

    /**
     * Enable Expanded
     *
     * @return \Omnyfy\VenndorSearch\Block\Search\Toolbar
     */
    public function enableExpanded()
    {
        $this->_isExpanded = true;
        return $this;
    }

    /**
     * Check is Expanded
     *
     * @return bool
     */
    public function isExpanded()
    {
        return $this->_isExpanded;
    }

    /**
     * Retrieve default per page values
     *
     * @return string (comma separated)
     */
    public function getDefaultPerPageValue()
    {
        if ($this->getCurrentMode() == 'list' && ($default = $this->getDefaultListPerPage())) {
            return $default;
        } elseif ($this->getCurrentMode() == 'grid' && ($default = $this->getDefaultGridPerPage())) {
            return $default;
        }
        return $this->_vendorListHelper->getDefaultLimitPerPageValue($this->getCurrentMode());
    }

    /**
     * Retrieve available limits for current view mode
     *
     * @return array
     */
    public function getAvailableLimit()
    {
        return $this->_vendorListHelper->getAvailableLimit($this->getCurrentMode());
    }

    /**
     * Get specified vendors limit display per page
     *
     * @return string
     */
    public function getLimit()
    {
        $limit = $this->_getData('_current_limit');
        if ($limit) {
            return $limit;
        }

        $limits = $this->getAvailableLimit();
        $defaultLimit = $this->getDefaultPerPageValue();
        if (!$defaultLimit || !isset($limits[$defaultLimit])) {
            $keys = array_keys($limits);
            $defaultLimit = $keys[0];
        }

        $limit = $this->toolbarMemorizer->getLimit();
        if (!$limit || !isset($limits[$limit])) {
            $limit = $defaultLimit;
        }

        if ($this->toolbarMemorizer->isMemorizingAllowed()) {
            $this->httpContext->setValue(ToolbarModel::LIMIT_PARAM_NAME, $limit, $defaultLimit);
        }

        $this->setData('_current_limit', $limit);
        return $limit;
    }

    /**
     * Check if limit is current used in toolbar.
     *
     * @param int $limit
     * @return bool
     */
    public function isLimitCurrent($limit)
    {
        return $limit == $this->getLimit();
    }

    /**
     * Pager number of items from which vendors started on current page.
     *
     * @return int
     */
    public function getFirstNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + 1;
    }

    /**
     * Pager number of items vendors finished on current page.
     *
     * @return int
     */
    public function getLastNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + $collection->count();
    }

    /**
     * Total number of vendors in current category.
     *
     * @return int
     */
    public function getTotalNum()
    {
        return $this->getCollection()->getSize();
    }

    /**
     * Check if current page is the first.
     *
     * @return bool
     */
    public function isFirstPage()
    {
        return $this->getCollection()->getCurPage() == 1;
    }

    /**
     * Return last page number.
     *
     * @return int
     */
    public function getLastPageNum()
    {
        return $this->getCollection()->getLastPageNumber();
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChildBlock('vendor_list_toolbar_pager');

        if ($pagerBlock instanceof \Magento\Framework\DataObject) {
            /* @var $pagerBlock \Magento\Theme\Block\Html\Pager */
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());

            $pagerBlock->setUseContainer(
                false
            )->setShowPerPage(
                false
            )->setShowAmounts(
                false
            )->setFrameLength(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setJump(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame_skip',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setLimit(
                $this->getLimit()
            )->setCollection(
                $this->getCollection()
            );

            return $pagerBlock->toHtml();
        }

        return '';
    }

    /**
     * Retrieve widget options in json format
     *
     * @param array $customOptions Optional parameter for passing custom selectors from template
     * @return string
     */
    public function getWidgetOptionsJson(array $customOptions = [])
    {
        $defaultMode = $this->_vendorListHelper->getDefaultViewMode($this->getModes());
        $options = [
            'mode' => ToolbarModel::MODE_PARAM_NAME,
            'direction' => ToolbarModel::DIRECTION_PARAM_NAME,
            'order' => ToolbarModel::ORDER_PARAM_NAME,
            'limit' => ToolbarModel::LIMIT_PARAM_NAME,
            'modeDefault' => $defaultMode,
            'directionDefault' => $this->_direction ?: Data::DEFAULT_SORT_DIRECTION,
            'orderDefault' => $this->getOrderField(),
            'limitDefault' => $this->_vendorListHelper->getDefaultLimitPerPageValue($defaultMode),
            'url' => $this->getPagerUrl(),
            'formKey' => $this->formKey->getFormKey(),
            'post' => $this->toolbarMemorizer->isMemorizingAllowed() ? true : false
        ];
        $options = array_replace_recursive($options, $customOptions);
        return json_encode(['vendorListToolbarForm' => $options]);
    }

    /**
     * Get order field
     *
     * @return null|string
     */
    protected function getOrderField()
    {
        if ($this->_orderField === null) {
            $this->_orderField = $this->_vendorListHelper->getDefaultSortField();
        }
        return $this->_orderField;
    }

    /**
     * Load Available Orders
     *
     * @return $this
     */
    private function loadAvailableOrders()
    {
        if ($this->_availableOrder === null) {
            $this->_availableOrder = $this->_catalogConfig->getAttributeUsedForSortByArray();
        }
        return $this;
    }
}
