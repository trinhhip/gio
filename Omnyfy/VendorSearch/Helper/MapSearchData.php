<?php

namespace Omnyfy\VendorSearch\Helper;


use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Omnyfy\Vendor\Model\Resource\Vendor;
use Omnyfy\VendorSearch\Model\Config\Source\System\MapStyle;
use Omnyfy\VendorSearch\Model\VendorSearch\ToolbarMemorizer;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;


class MapSearchData extends AbstractHelper
{
    const VENDOR_MAP_SEARCH_DISTANCE = 'vendor_map_search_distance';

    const VENDOR_LATITUDE = 'latitude';

    const VENDOR_LONGITUDE = 'longitude';

    const XML_PATH = 'vendor_map_search/';

    /**
     * Clock group
     */
    const DEFAULT_MAP_MODE = 'vendor_map_search/clock/default';

    const IS_ENABLED = 'vendor_map_search/clock/enabled';

    /**
     * Map settings group
     */
    const GMAP_API_KEY         = 'google/maps/key';
    const GMAP_TYPE            = 'map_setting/map_type';
    const GMAP_STYLE           = 'map_setting/map_style';
    const GMAP_DEFAULT_ZOOM    = 'map_setting/default_zoom';
    const GMAP_VENDOR_MARKER_ICON     = 'map_setting/vendor_marker_icon';
    const GMAP_ADDRESS_MARKER_ICON     = 'map_setting/address_marker_icon';
    const GMAP_VENDOR_ATTR    = 'map_setting/attribute';
    const GMAP_DEFAULT_CENTER  = 'map_setting/default_center';
    const GMAP_SEARCH_DISTANCE = 'map_setting/search_distance';
    const ALLOWED_COUNTRIES = 'map_setting/allow';

    /**
     * Develop group
     */
    const CONTENT_MAP_AREA = 'map_develop/content_map_area';
    const LAYER_CONTAINER  = 'map_develop/layer_container';
    const LAYER_COUNTER_CONTAINER = 'map_develop/layer_counter_container';
    const VENDOR_SEARCH_DATA = 'mapSearchData';
    const DEFAULT_ADDRESS_MARKER_ICON_PATH = 'Omnyfy_VendorSearch::images/location.png';

    protected $_toolbarMemorizer;

    protected $_request;

    protected $_vendorSearchHelper;
    protected $_urlInterface;
    protected $_mapStyle;
    protected $_jsonHelper;
    protected $_storeManager;
    protected $_vendorResource;
    protected $_cookieManager;

    public function __construct(
        ToolbarMemorizer $toolbarMemorizer,
        RequestInterface $request,
        Data $vendorSearchHelper,
        UrlInterface $urlInterface,
        MapStyle $mapStyle,
        JsonHelper $jsonHelper,
        StoreManagerInterface $storeManager,
        Vendor $vendorResource,
        CookieManagerInterface $cookieManager,
        Context $context
    )
    {
        $this->_toolbarMemorizer = $toolbarMemorizer;
        $this->_request = $request;
        $this->_vendorSearchHelper = $vendorSearchHelper;
        $this->_urlInterface = $urlInterface;
        $this->_mapStyle = $mapStyle;
        $this->_jsonHelper = $jsonHelper;
        $this->_storeManager = $storeManager;
        $this->_vendorResource = $vendorResource;
        $this->_cookieManager = $cookieManager;
        parent::__construct($context);
    }

    /**
     * Get distance expression.
     *
     * @param float $latitude
     * @param float $longitude
     *
     * @return \Zend_Db_Expr
     */
    public function getDistanceExpression($latitude, $longitude)
    {
        return new \Zend_Db_Expr("
            SQRT(
                POW(69.1 * (". self::VENDOR_LATITUDE ." - {$latitude}), 2) +
                POW(69.1 * ({$longitude} - ". self::VENDOR_LONGITUDE .") * COS(". self::VENDOR_LATITUDE ." / 57.3), 2)
            )
        ");
    }

    /**
     * Get Google map search center
     *
     * @param integer|null $storeId
     * @p
     *
     * @return mixed
     */
    public function getGmapSearchCenter($storeId = null)
    {
        $defaultLatLong = $this->getStoreConfig(
            self::GMAP_DEFAULT_CENTER, $storeId
        );
        $dataLatitude = $dataLongitude = null;

        if (!empty($defaultLatLong)) {
            $defaultPosition = explode(',', $defaultLatLong);
            if (is_array($defaultPosition)) {
                $dataLatitude = $defaultPosition[0];
                $dataLongitude = $defaultPosition[1];
            }
        }
        /** @var RequestInterface $request */
        $params = $this->_request->getParams();
        $latitude = $this->isCoordinateParamExist($params) ? $params['latitude'] : $dataLatitude;
        $longitude = $this->isCoordinateParamExist($params) ? $params['longitude'] : $dataLongitude;

        if(!$this->isCoordinateParamExist($params)){
            $vendorSearchDataArr = $this->getVendorSearchDataArr();
            if(!empty($vendorSearchDataArr)){
                $latitude = $vendorSearchDataArr['geometry']['location']['lat'];
                $longitude = $vendorSearchDataArr['geometry']['location']['lng'];
            }
        }
        if (!empty($latitude) && !empty($longitude)) {
            return $latitude . ',' . $longitude;
        }
        //Return default location if empty config
        return '0.0000,0.0000';
    }


    public function isCoordinateParamExist($params){
        return !empty($params['latitude']) && !empty($params['longitude']);
    }

    /**
     * Get store config
     *
     * @param string $code
     * @param integer|null $storeId
     *
     * @return mixed
     */
    public function getStoreConfig($code, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH . $code, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * Get Google map search distance
     *
     * @param integer|null $storeId
     *
     * @return mixed
     */
    public function getGmapSearchDistance($storeId = null)
    {
        $distance = null;
        $distanceOptionId = $this->_request->getParam(self::VENDOR_MAP_SEARCH_DISTANCE);
        if($distanceOptionId){
            $mapSearchDistanceAtt = $this->_vendorResource->getAttribute(self::VENDOR_MAP_SEARCH_DISTANCE);
            if($mapSearchDistanceAtt->usesSource()){
                $distance = $mapSearchDistanceAtt->getSource()->getOptionText($distanceOptionId);
            }
        }
        return $distance;
    }

    public function getCurrentVendorSearchViewMode()
    {
        $modes = $this->_vendorSearchHelper->getAvailableViewMode();
        $defaultMode = $this->_vendorSearchHelper->getDefaultViewMode($modes);
        $mode = $this->_toolbarMemorizer->getMode();
        if (!$mode) {
            $mode = $defaultMode;
        }
        return $mode;
    }

    public function isEnabled(){
        return $this->scopeConfig->getValue(
            self::IS_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlInterface->getCurrentUrl();
    }

    /**
     * Get address which customer entered
     *
     * @return string
     */
    public function getRequestAddress()
    {
        $vendorSearchDataArr = $this->getVendorSearchDataArr();
        return !empty($vendorSearchDataArr['requestAddress']) ? $vendorSearchDataArr['requestAddress'] : '';
    }

    public function getVendorSearchDataArr(){
        $vendorSearchData = $this->getCookie(self::VENDOR_SEARCH_DATA);
        $vendorSearchDataArr = [];
        if(isset($vendorSearchData)){
            $vendorSearchDataArr = $this->jsonDecode($vendorSearchData);
        }
        return $vendorSearchDataArr;
    }
    /**
     * Get Google map api key
     *
     * @param integer|null $storeId
     *
     * @return mixed
     */
    public function getGmapAPiKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::GMAP_API_KEY, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * Get Google map type
     *
     * @param integer|null $storeId
     *
     * @return mixed
     */
    public function getGmapType($storeId = null)
    {
        return $this->getStoreConfig(
            self::GMAP_TYPE, $storeId
        );
    }

    /**
     * Get Map theme
     *
     * @param $styleName
     *
     * @return string
     */
    public function getMapTheme($styleName)
    {
        return $this->_mapStyle->getMapData($styleName);
    }

    /**
     * Get map style
     *
     * @param int|null $storeId
     *
     * @return false|string
     */
    public function getGmapStyle($storeId = null)
    {
        $mapStyle = $this->getStoreConfig(
            self::GMAP_STYLE, $storeId
        );

        return self::jsonEncode($this->getMapTheme($mapStyle));
    }

    /**
     * Get Google map default zoom
     *
     * @param integer|null $storeId
     *
     * @return mixed
     */
    public function getGmapDefaultZoom($storeId = null)
    {
        return $this->getStoreConfig(
            self::GMAP_DEFAULT_ZOOM, $storeId
        );
    }

    /**
     * Get Google map vendor attribute
     *
     * @param integer|null $storeId
     *
     * @return mixed
     */
    public function getGmapProductAttr($storeId = null)
    {
        return $this->getStoreConfig(
            self::GMAP_VENDOR_ATTR, $storeId
        );
    }

    /**
     * Get Google map default zoom
     *
     * @param integer|null $storeId
     *
     * @return mixed
     */
    public function getGmapMakerIcon($storeId = null, $configPath = self::GMAP_VENDOR_MARKER_ICON)
    {
        $store =  $this->_storeManager->getStore();
        $mediaUrl = $store->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . 'omnyfy/vendorsearch/marker_icon/';
        $iconPath = $this->getStoreConfig(
            $configPath, $storeId
        );
        return $iconPath ? $mediaUrl . $this->getStoreConfig($configPath, $storeId) : null;
    }

    /**
     * get Content Map Area
     *
     * @param integer|null $storeId
     *
     * @return mixed
     */
    public function getContentMapArea($storeId = null)
    {
        return $this->getStoreConfig(
            self::CONTENT_MAP_AREA, $storeId
        );
    }

    /**
     * get Layer Container
     *
     * @param integer|null $storeId
     *
     * @return mixed
     */
    public function getLayerContainer($storeId = null)
    {
        return $this->getStoreConfig(
            self::LAYER_CONTAINER, $storeId
        );
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     *
     * @return string
     */
    public function jsonEncode($valueToEncode)
    {
        try {
            $encodeValue = $this->getJsonHelper()->jsonEncode($valueToEncode);
        } catch (\Exception $e) {
            $encodeValue = '{}';
        }

        return $encodeValue;
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     *
     * @return string
     */
    public function jsonDecode($valueToDecode)
    {
        try {
            $valueToDecode = $this->getJsonHelper()->jsonDecode($valueToDecode);
        } catch (\Exception $e) {
            $valueToDecode = null;
        }

        return $valueToDecode;
    }

    /**
     * Get Json Helper
     *
     * @return JsonHelper|mixed
     */
    public function getJsonHelper()
    {
        return $this->_jsonHelper;
    }

    public function getMarkerHeight()
    {
        $height = 50;
        if($this->getGmapMakerIcon()){
            $imageData = getimagesize($this->getGmapMakerIcon());
            $height = $imageData[1];
        }
        return $height;
    }

    public function getVendorCounterContainer($storeId = null)
    {
        return $this->getStoreConfig(
            self::LAYER_COUNTER_CONTAINER, $storeId
        );
    }

    public function getCookie($name){
        return $this->_cookieManager->getCookie($name);
    }

    public function getDefaultDistance($storeId = null)
    {
        return $this->getStoreConfig(
            self::GMAP_SEARCH_DISTANCE, $storeId
        );
    }

    public function getAllowCountries($storeId = null)
    {
        return $this->getStoreConfig(self::ALLOWED_COUNTRIES, $storeId);
    }
}