<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 14/01/2020
 * Time: 1:40 PM
 */

namespace Omnyfy\VendorSearch\Helper;


use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Omnyfy\Vendor\Api\VendorTypeRepositoryInterface;
use Omnyfy\VendorSearch\Api\MapInterface;
use Omnyfy\VendorSearch\Model\Provider\Layer;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const IS_ENABLED = 'vendor_search/options/is_active';
    const PAGE_TITLE = 'vendor_search/search_result/page_title';
    const IS_SEARCH_FORM = 'vendor_search/search_result/is_search_form';
    const IS_FILTERS = 'vendor_search/search_result/is_filters';
    const IS_DISTANCE = 'vendor_search/search_result/is_filter_distance';
    const LOCATION_URL = 'vendor_search/search_result/location_page';
    const LOCATION_LOCATION_URL = 'omnyfy_vendor/index/location';

    const BOOKING_LOCATION_URL = 'booking/practice/view';
    const VENDOR_URL = 'shop/brands/view';

    const DEFAULT_SORT_DIRECTION = 'asc';
    protected $_vendorTypeRepository;

    public function __construct(
        Context $context,
        VendorTypeRepositoryInterface $vendorTypeRepository
    )
    {
        $this->_vendorTypeRepository = $vendorTypeRepository;
        parent::__construct($context);
    }

    public function isEnabled(){
        return $this->scopeConfig->getValue(
            self::IS_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isSearchForm(){
        return $this->scopeConfig->getValue(
            self::IS_SEARCH_FORM,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isFilters(){
        return $this->scopeConfig->getValue(
            self::IS_FILTERS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isDistance(){
        return $this->scopeConfig->getValue(
            self::IS_DISTANCE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPageTitle(){
        $title = $this->scopeConfig->getValue(
            self::PAGE_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (empty($title))
            return "Search Results";

        return $title;
    }

    public function getLocationUrl(){
        $urlId = $this->scopeConfig->getValue(
            self::LOCATION_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($urlId == 1)
            return self::LOCATION_LOCATION_URL;

        if ($urlId == 2)
            return self::BOOKING_LOCATION_URL;
    }

    /**
     * Returns default view mode
     *
     * @param array $options
     * @return string
     */
    public function getDefaultViewMode($options = [])
    {
        if (empty($options)) {
            $options = $this->getAvailableViewMode();
        }
        $mapSearchEnable = $this->scopeConfig->getValue(MapSearchData::IS_ENABLED, ScopeInterface::SCOPE_STORE);
        $isMapDefaultMode = $this->scopeConfig->getValue(MapSearchData::DEFAULT_MAP_MODE, ScopeInterface::SCOPE_STORE);
        return $mapSearchEnable && $isMapDefaultMode ? MapInterface::MODE_NAME : current(array_keys($options));
    }

    /**
     * Get the view mode for the vendor type
     * 0 - Grid view (defaulted)
     * 1 - List view
     * @return int|null|string
     */
    public function getVendorViewMode(){
        if ($vendorType = $this->getVendorType())
            return $vendorType->getViewMode();
        return Layer::VIEW_GRID;
    }

    /**
     * Get the vendorType by the url parameter
     * @return mixed|null|\Omnyfy\Vendor\Api\Data\VendorTypeInterface
     */
    public function getVendorType(){
        try {
            if ($vendorTypeId = $this->_request->getParam("type")) {
                if (isset($this->_vendorTypes[$vendorTypeId])) {
                    return $this->_vendorTypes[$vendorTypeId];
                }
                $vendorType = $this->_vendorTypeRepository->getById($vendorTypeId);
                $this->_vendorTypes[$vendorTypeId] = $vendorType;
                return $vendorType;
            }
        } catch(\Exception $exception){
            return null;
        }
    }

    /**
     * Returns available mode for view
     *
     * @return array|null
     */
    public function getAvailableViewMode()
    {
        $value = $this->getVendorViewMode();
        $mapSearchEnable = $this->scopeConfig->getValue(MapSearchData::IS_ENABLED, ScopeInterface::SCOPE_STORE);

        switch ($value) {
            case Layer::VIEW_LIST:
                return ($mapSearchEnable) ? ['list' => __('List'), MapInterface::MODE_NAME => __('Map')] : ['list' => __('List')];

            case Layer::VIEW_GRID:
                return ($mapSearchEnable) ? ['grid' => __('Grid'), MapInterface::MODE_NAME => __('Map')] : ['grid' => __('Grid')];
        }

        return null;
    }
}