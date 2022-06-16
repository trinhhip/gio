<?php
/**
 * Project: Omnyfy Multi Vendor.
 * User: jing
 * Date: 11/4/17
 * Time: 11:09 AM
 */
namespace Omnyfy\Vendor\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $resource;

    protected $locationFactory;

    protected $vendorFactory;

    protected $locationIds;

    protected $orderFactory;

    protected $quoteFactory;

    protected $vendorResource;

    protected $invoiceFactory;

    protected $orderTaxManagement;

    protected $inventoryResource;

    protected $vendorConfig;

    protected $shippingHelper;

    protected $vSourceStockCollectionFactory;

    protected $sourceCollectionFactory;

    private $emailHelper;

    private $storeManager;

    protected $vSourceStockResource;
    const XML_PATH_CONFIG_SHOW_LOCATION_NAME_IN_CART = 'omnyfy_vendor/vendor/show_location_name_in_cart';
    /**
     * @var array
     */
    private $shippingData = [];
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $backendSession;

    public function __construct(
        Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Omnyfy\Vendor\Model\LocationFactory $locationFactory,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource,
        \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory,
        \Magento\Tax\Api\OrderTaxManagementInterface $orderTaxManagement,
        \Omnyfy\Vendor\Model\Resource\Inventory $inventoryResource,
        \Omnyfy\Vendor\Model\Config $vendorConfig,
        \Omnyfy\Vendor\Helper\Shipping $shippingHelper,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock\CollectionFactory $vSourceStockCollectionFactory,
        \Magento\Inventory\Model\Source $source,
        \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Omnyfy\Core\Helper\Email $emailHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource,
        \Magento\Backend\Model\SessionFactory $backendSession
    ) {
        $this->resource = $resource;

        $this->locationFactory = $locationFactory;

        $this->vendorFactory = $vendorFactory;

        $this->orderFactory = $orderFactory;

        $this->quoteFactory = $quoteFactory;

        $this->vendorResource = $vendorResource;

        $this->invoiceFactory = $invoiceFactory;

        $this->orderTaxManagement = $orderTaxManagement;

        $this->inventoryResource = $inventoryResource;

        $this->vendorConfig = $vendorConfig;

        $this->shippingHelper = $shippingHelper;

        $this->vSourceStockCollectionFactory = $vSourceStockCollectionFactory;

        $this->sourceModel = $source;

        $this->sourceCollectionFactory = $sourceCollectionFactory;

        $this->emailHelper = $emailHelper;

        $this->storeManager = $storeManager;

        $this->vSourceStockResource = $vSourceStockResource;

        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

    public function getTaxNumberByVendorId($vendorId)
    {
        $connection = $this->resource->getConnection();
        $select = $connection
            ->select()->from(['kyc' => $connection->getTableName('omnyfy_vendor_kyc_details')], '')
            ->join(['sign' => $connection->getTableName('omnyfy_vendor_signup')], 'kyc.signup_id = sign.id', 'tax_number')
            ->where('vendor_id = ?', $vendorId);
        return $connection->fetchCol($select)[0] != '' ? $connection->fetchCol($select)[0] : 'Tax Number';
    }

    public function getVendorSignUp($vendorId)
    {
        $connection = $this->resource->getConnection();
        $select = $connection
            ->select()->from(['kyc' => $connection->getTableName('omnyfy_vendor_kyc_details')], '')
            ->join(['sign' => $connection->getTableName('omnyfy_vendor_signup')], 'kyc.signup_id = sign.id', ['*'])
            ->where('vendor_id = ?', $vendorId);
        return $connection->fetchRow($select);
    }
    
    /**
     * @param string $field
     * @param null|int $storeId
     * @return string
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isShowLocationNameInCart()
    {
        return $this->getConfigValue(self::XML_PATH_CONFIG_SHOW_LOCATION_NAME_IN_CART);
    }


    public function checkMultipleLocationByVendorId($vendorId)
    {
        $collection = $this->vSourceStockCollectionFactory->create()->getCollection();
        $collection->addFieldToSelect('vendor_id');
        $collection->addFieldToFilter('vendor_id', $vendorId);
        if ($collection->getSize() > 1) {
            return true;
        }
        return false;
    }


    public function loadLocationIds($websiteId)
    {
        if (isset($this->locationIds[$websiteId])
            && is_array($this->locationIds[$websiteId])
            && !empty($this->locationIds[$websiteId])
        ) {
            return $this->locationIds[$websiteId];
        }

        $collection = $this->locationFactory->create()->getCollection();
        $collection->filterWebsite($websiteId);
        $collection->getSelect()->order('priority', 'desc');

        $result = [];
        foreach ($collection as $location) {
            $result[$location->getId()] = [
                'vendor_id' => $location->getVendorId(),
                'priority' => $location->getPriority()
            ];
        }
        $this->locationIds[$websiteId] = $result;
        return $result;
    }

    public function groupInventoryByLocationId($productId, $websiteId, &$vendorId, $activeVendorOnly = false, $activeLocationOnly = false)
    {
        return $this->inventoryResource->loadInventoryGroupedByLocation($productId, $websiteId, $vendorId, $activeVendorOnly, $activeLocationOnly);
    }

    public function getRoleIdsByName($roleName, $resource = null)
    {
        if (empty($roleName)) {
            return [];
        }

        if (null == $resource) {
            $resource = $this->resource;
        }

        $table = 'authorization_role';

        if (method_exists($resource, 'getTable')) {
            $table = $resource->getTable($table);
        } elseif (method_exists($resource, 'getTableName')) {
            $table = $resource->getTableName($table);
        }

        $connection = $resource->getConnection();

        $select = $connection->select()->from(
            $table,
            ['role_id']
        )->where(
            "role_name = ?",
            $roleName
        )->where(
            "role_type = ?",
            RoleGroup::ROLE_TYPE
        )->where(
            "user_type = ?",
            UserContextInterface::USER_TYPE_ADMIN
        );

        $roleIds = $connection->fetchCol($select);
        return $roleIds;
    }

    public function getStoreIdsByWebsiteIds($websiteIds)
    {
        $table = $this->resource->getTableName('store');
        $conn = $this->resource->getConnection();

        $select = $conn->select()->from(
            $table,
            ['store_id']
        )->where(
            "website_id in (?)",
            $websiteIds
        );

        $storeIds = $conn->fetchCol($select);
        return $storeIds;
    }

    public function convertShippingMethods($method, &$compareLocation)
    {
        $compareLocation = true;
        if (is_array($method)) {
            return $method;
        } elseif (is_string($method)) {
            if ('{' == substr($method, 0, 1)) {
                return $this->shippingMethodStringToArray($method);
            } else {
                $compareLocation = false;
                return [$method];
            }
        } else {
            return [];
        }
    }

    public function shippingMethodArrayToString($shippingMethodArray)
    {
        if (is_array($shippingMethodArray) && !empty($shippingMethodArray)) {
            return json_encode($shippingMethodArray);
        }
        return '';
    }

    public function shippingMethodStringToArray($shippingMethodString)
    {
        if (is_string($shippingMethodString)
            && '{' == substr($shippingMethodString, 0, 1)
            && '}' == substr($shippingMethodString, -1)
        ) {
            return json_decode($shippingMethodString, true);
        }
        return null;
    }

    public function getCarrierCode($shippingMethodString)
    {
        $methods = $this->shippingMethodStringToArray($shippingMethodString);
        if (empty($methods)) {
            return '';
        }
        $codes = [];
        foreach ($methods as $locationId => $code) {
            $codeArr = explode('_', $code);
            $codes[$locationId] = $codeArr[0];
        }
        return json_encode($codes);
    }

    public function getMethodCode($shippingMethodString)
    {
        $methods = $this->shippingMethodStringToArray($shippingMethodString);
        if (empty($methods)) {
            return '';
        }
        $codes = [];
        foreach ($methods as $locationId => $code) {
            $codeArr = explode('_', $code);
            $codes[$locationId] = $codeArr[1];
        }
        return json_encode($codes);
    }

    public function parseCodeToShippingMethodString($carrierCode, $methodCode)
    {
        if ('{' != substr($carrierCode, 0, 1) && '{' != substr($methodCode, 0, 1)) {
            return $carrierCode . '_' . $methodCode;
        }

        $cCodes = json_decode($carrierCode, true);
        $mCodes = json_decode($methodCode, true);

        $result = [];
        foreach ($cCodes as $locationId => $code) {
            $result[$locationId] = $code . '_' . $mCodes[$locationId];
        }

        return $this->shippingMethodArrayToString($result);
    }

    public function shippingMethodArrayCodeToRateId($shippingMethodArray, $rates)
    {
        $result = [];
        foreach ($shippingMethodArray as $locationId => $code) {
            foreach ($rates as $rate) {
                if ($locationId == $rate->getLocationId() && $code == $rate->getCode()) {
                    $result[$locationId] = $rate->getId();
                    break;
                }
            }
        }
        return $result;
    }

    public function getLimitCarrier($shippingMethodString)
    {
        $result = [];
        $flag = true;
        $methods = $this->convertShippingMethods($shippingMethodString, $flag);
        foreach ($methods as $locationId => $code) {
            list($carrierCode, $methodCode) = explode('_', $code);
            $result[] = $carrierCode;
        }
        return array_unique($result);
    }

    public function getLocationIds($quote)
    {
        $locationIds = [];
        foreach ($quote->getAllItems() as $item) {
            $locationIds[]= $item->getLocationId();
        }
        return array_unique($locationIds);
    }

    public function getSourceStockId($quote)
    {
        $sourceStockIds = [];
        foreach ($quote->getAllItems() as $item) {
            $sourceStockIds[]= $item->getSourceStockId();
        }
        return array_unique($sourceStockIds);
    }

    public function getLocationsInfo($items)
    {
        $toVendorIds = [];
        foreach ($items as $item) {
            $toVendorIds[$item->getLocationId()] = $item->getVendorId();
        }


        $vendorIds = array_unique(array_values($toVendorIds));
        $locationIds = array_unique(array_keys($toVendorIds));
        $vendors = $this->getVendorsByIds($vendorIds);

        $locations = $this->getLocationsByIds($locationIds);
        $result = [];
        foreach ($locations as $location) {
            if ($location->getVendorId() != $toVendorIds[$location->getId()]) {
                $vendor = $vendors->getItemById($toVendorIds[$location->getId()]);
                $vendorName = empty($vendor) ? $this->getVendorNameById($toVendorIds[$location->getId()]) : $vendor->getName();
                $vendorName = empty($vendorName) ? '' : $vendorName;
                $location->setData('vendor_name', $vendorName);
            }
            $result[] = $location;
        }

        return $result;
    }

    public function getSourceInfo($items)
    {
        $toVendorIds = [];

        foreach ($items as $item) {
            $toVendorIds[$item->getSourceStockId()] = $item->getVendorId();
        }

        $vendorIds = array_unique(array_values($toVendorIds));
        $sourceIds = array_unique(array_keys($toVendorIds));
        $vendors = $this->getVendorsByIds($vendorIds);

        $sources = $this->getSourcesByIds($sourceIds);
        $result = [];
        foreach ($sources as $source) {
            if ($source->getVendorId() != $toVendorIds[$source->getId()]) {
                $vendor = $vendors->getItemById($toVendorIds[$source->getId()]);
                $vendorName = empty($vendor) ? $this->getVendorNameById($toVendorIds[$source->getId()]) : $vendor->getName();
                $vendorName = empty($vendorName) ? '' : $vendorName;
                $source->setData('vendor_name', $vendorName);
            }
            $result[] = $source;
        }

        return $result;
    }

    public function getSourceObj($sourceCode)
    {
        $collection = $this->sourceCollectionFactory->create();

        if ($sourceCode) {
            $model = $collection->getItemByColumnValue('source_code', $sourceCode);

            return $model;
        } else {
            return null;
        }
    }

    public function getSourcesByIds($ids)
    {
        if (empty($ids)) {
            return false;
        }
        if (!is_array($ids)) {
            $ids = [intval($ids)];
        }
        $collection = $this->vSourceStockCollectionFactory->create();
        $collection->addFieldToSelect('*');
        $collection->addFieldToFilter('id', ['in' => $ids]);

        $a = $collection->getData();

        return $collection;
    }

    public function getSourceByIds($id)
    {
        if (empty($ids)) {
            return false;
        }
        if (!is_array($ids)) {
            $ids = [intval($ids)];
        }
        $collection = $this->vSourceStockCollectionFactory->create();
        $collection->addFieldToSelect('*');
        $collection->addFieldToFilter('id', ['eq' => $id]);

        return $collection;
    }

    public function getBookingSourceStockIds($items)
    {
        $bookingSourceStockIds = $noneBookingSourceStockIds = [];
        foreach ($items as $item) {
            $toVendorIds[$item->getSourceStockId()] = $item->getVendorId();
            if (!empty($item->getBookingId())) {
                $bookingSourceStockIds[] = $item->getSourceStockId();
            } else {
                $noneBookingSourceStockIds[] = $item->getSourceStockId();
            }
        }
        $bookingLocationIds = array_unique($bookingSourceStockIds);
        $noneBookingLocationIds = array_unique($noneBookingSourceStockIds);
        return array_diff($bookingSourceStockIds, $noneBookingSourceStockIds);
    }

    public function getLocationsByIds($locationIds)
    {
        if (empty($locationIds)) {
            return false;
        }
        if (!is_array($locationIds)) {
            $locationIds = [intval($locationIds)];
        }
        $collection = $this->locationFactory->create()->getCollection();
        $collection->addFieldToSelect('*');
        $collection->addFieldToFilter('entity_id', ['in' => $locationIds]);
        $collection->joinVendorInfo();

        return $collection;
    }

    public function getVendorsByIds($vendorIds)
    {
        if (empty($vendorIds)) {
            return false;
        }
        if (!is_array($vendorIds)) {
            $vendorIds = [intval($vendorIds)];
        }
        $collection = $this->vendorFactory->create()->getCollection();
        $collection->addFieldToFilter('entity_id', $vendorIds);

        return $collection;
    }

    public function groupItemsByLocation($items)
    {
        $result = [];
        foreach ($items as $item) {
            $locationId = intval($item->getLocationId());
            if (empty($locationId)) {
                continue;
            }
            if (!array_key_exists($locationId, $result)) {
                $result[$locationId] = [];
            }
            $result[$locationId][] = $item;
        }
        return $result;
    }

    public function calculateVendorOrderTotal($orderId)
    {
        $order = $this->orderFactory->create();
        $order->load($orderId);
        if (!$order->getId()) {
            return false;
        }

        $items = $order->getAllItems();
        $total = [];
        $locationIds = [];
        foreach ($items as $item) {
            $locationIds[] = $item->getLocationId();
            $vendorId = $item->getVendorId();
            if (!isset($total[$vendorId])) {
                $total[$vendorId] = [
                    'subtotal'              => 0.0,
                    'base_subtotal'         => 0.0,
                    'subtotal_incl_tax'     => 0.0,
                    'base_subtotal_incl_tax'=> 0.0,
                    'tax_amount'            => 0.0,
                    'base_tax_amount'       => 0.0,
                    'shipping_amount'       => 0.0,
                    'base_shipping_amount'  => 0.0,
                    'shipping_incl_tax'     => 0.0,
                    'base_shipping_incl_tax'=> 0.0,
                    'discount_amount'       => 0.0,
                    'base_discount_amount'  => 0.0,
                    'shipping_tax'          => 0.0,
                    'base_shipping_tax'     => 0.0,
                    'grand_total'           => 0.0,
                    'base_grand_total'      => 0.0
                ];
            }
            $total[$vendorId]['subtotal'] += $item->getRowTotal();
            $total[$vendorId]['base_subtotal'] += $item->getBaseRowtotal();
            $total[$vendorId]['subtotal_incl_tax'] += $item->getRowTotalInclTax();
            $total[$vendorId]['base_subtotal_incl_tax'] += $item->getBaseRowTotalInclTax();
            $total[$vendorId]['tax_amount'] += $item->getTaxAmount();
            $total[$vendorId]['base_tax_amount'] += $item->getBaseTaxAmount();
            $total[$vendorId]['discount_amount'] += $item->getDiscountAmount();
            $total[$vendorId]['base_discount_amount'] += $item->getBaseDiscountAmount();
        }

        $shippingPickupLocation = $this->shippingHelper->getShippingConfiguration('overall_pickup_location');
        if ($this->shippingHelper->getCalculateShippingBy() == 'overall_cart') {
            $locationIds = [];
            array_push($locationIds, $shippingPickupLocation);
        } else {
            $locationIds = array_unique($locationIds);
        }


        $included = $order->getShippingInclTax() - $order->getShippingAmount() > 0.0001 ? false : true;
        $baseToOrderRate = $order->getBaseToOrderRate();

        if ($order->getShippingAmount() > 0) {
            // load all shipping tax percentage
            $percentages = $this->getShippingTaxPercent($orderId);

            $quoteId = $order->getQuoteId();
            $quote = $this->quoteFactory->create();
            $quote->load($quoteId);

            $shippingAddress = $quote->getShippingAddress();
            $rates = $shippingAddress->getAllShippingRates();

            $shippingMethod = $this->shippingMethodStringToArray($order->getShippingMethod());
            $shippingMethod = empty($shippingMethod) ? [$locationIds[0] => $order->getShippingMethod()] : $shippingMethod;

            foreach ($shippingMethod as $locationId => $code) {
                if ($this->shippingHelper->getCalculateShippingBy() != 'overall_cart') {
                    foreach ($rates as $rate) {
                        if ($rate->getLocationId() == $locationId && $code == $rate->getCode()) {
                            $vendorId = $rate->getVendorId();
                            $total[$vendorId]['shipping_amount'] += $rate->getPrice();
                            $total[$vendorId]['base_shipping_amount'] += $rate->getPrice() / $baseToOrderRate;

                            foreach ($percentages as $taxCode => $percentage) {
                                $shippingTaxAmount = $this->getTaxAmount($rate->getPrice(), $percentage, $included);
                                $total[$vendorId]['shipping_tax'] += $shippingTaxAmount;
                                $total[$vendorId]['base_shipping_tax'] += $shippingTaxAmount / $baseToOrderRate;
                            }
                        }
                    }
                }
            }
        }

        if ($this->shippingHelper->getCalculateShippingBy() != 'overall_cart') {
            foreach ($total as $vendorId => $totalData) {
                if ($included) {
                    $total[$vendorId]['shipping_incl_tax'] = $total[$vendorId]['shipping_amount'];
                    $total[$vendorId]['base_shipping_incl_tax'] = $total[$vendorId]['base_shipping_amount'];
                } else {
                    $total[$vendorId]['shipping_incl_tax'] =
                        $total[$vendorId]['shipping_amount'] + $total[$vendorId]['shipping_tax'];
                    $total[$vendorId]['base_shipping_incl_tax'] =
                        $total[$vendorId]['base_shipping_amount'] + $total[$vendorId]['base_shipping_tax'];
                }
                $total[$vendorId]['grand_total'] = $total[$vendorId]['subtotal_incl_tax'] + $total[$vendorId]['shipping_incl_tax'];
                $total[$vendorId]['base_grand_total'] = $total[$vendorId]['base_subtotal_incl_tax'] + $total[$vendorId]['base_shipping_incl_tax'];
            }
        }

        $totalData = [];
        foreach ($total as $vendorId => $totalArr) {
            $totalData[] = [
                'vendor_id'             => $vendorId,
                'order_id'              => $orderId,
                'subtotal'              => $totalArr['subtotal'],
                'base_subtotal'         => $totalArr['base_subtotal'],
                'subtotal_incl_tax'     => $totalArr['subtotal_incl_tax'],
                'base_subtotal_incl_tax'=> $totalArr['base_subtotal_incl_tax'],
                'tax_amount'            => $totalArr['tax_amount'],
                'base_tax_amount'       => $totalArr['base_tax_amount'],
                'discount_amount'       => $totalArr['discount_amount'],
                'base_discount_amount'  => $totalArr['base_discount_amount'],
                'shipping_amount'       => $totalArr['shipping_amount'],
                'base_shipping_amount'  => $totalArr['base_shipping_amount'],
                'shipping_incl_tax'     => $totalArr['shipping_incl_tax'],
                'base_shipping_incl_tax'=> $totalArr['base_shipping_incl_tax'],
                'shipping_tax'          => $totalArr['shipping_tax'],
                'base_shipping_tax'     => $totalArr['base_shipping_tax'],
                'grand_total'           => $totalArr['grand_total'],
                'base_grand_total'      => $totalArr['base_grand_total']
            ];
        }

        //save vendor order total into database
        $this->vendorResource->saveOrderTotal($totalData, [
            'subtotal',
            'base_subtotal',
            'subtotal_incl_tax',
            'base_subtotal_incl_tax',
            'tax_amount',
            'base_tax_amount',
            'discount_amount',
            'base_discount_amount',
            'shipping_amount',
            'base_shipping_amount',
            'shipping_incl_tax',
            'base_shipping_incl_tax',
            'shipping_tax',
            'base_shipping_tax',
            'grand_total',
            'base_grand_total'
        ]);

        return true;
    }

    public function calculateVendorInvoiceTotal($invoiceId)
    {
        $invoice = $this->invoiceFactory->create();
        $invoice->load($invoiceId);
        if (!$invoice->getId()) {
            return false;
        }

        $items = $invoice->getAllItems();
        $total = [];
        $locationIds = [];
        foreach ($items as $item) {
            /** @var $item \Magento\Sales\Model\Order\Invoice\Item */
            $locationIds[] = $item->getLocationId();
            $vendorId = $item->getVendorId();
            if (!isset($total[$vendorId])) {
                $total[$vendorId] = [
                    'subtotal'              => 0.0,
                    'base_subtotal'         => 0.0,
                    'subtotal_incl_tax'     => 0.0,
                    'base_subtotal_incl_tax'=> 0.0,
                    'tax_amount'            => 0.0,
                    'base_tax_amount'       => 0.0,
                    'shipping_amount'       => 0.0,
                    'base_shipping_amount'  => 0.0,
                    'shipping_incl_tax'     => 0.0,
                    'base_shipping_incl_tax'=> 0.0,
                    'discount_amount'       => 0.0,
                    'base_discount_amount'  => 0.0,
                    'shipping_tax'          => 0.0,
                    'base_shipping_tax'     => 0.0,
                    'grand_total'           => 0.0,
                    'base_grand_total'      => 0.0
                ];
            }
            $total[$vendorId]['subtotal'] += $item->getRowTotal();
            $total[$vendorId]['base_subtotal'] += $item->getBaseRowtotal();
            $total[$vendorId]['subtotal_incl_tax'] += $item->getRowTotalInclTax();
            $total[$vendorId]['base_subtotal_incl_tax'] += $item->getBaseRowTotalInclTax();
            $total[$vendorId]['tax_amount'] += $item->getTaxAmount();
            $total[$vendorId]['base_tax_amount'] += $item->getBaseTaxAmount();
            $total[$vendorId]['discount_amount'] += $item->getDiscountAmount();
            $total[$vendorId]['base_discount_amount'] += $item->getBaseDiscountAmount();
        }

        $included = $invoice->getShippingInclTax() - $invoice->getShippingAmount() > 0.0001 ? false : true;
        $baseToOrderRate = $invoice->getBaseToOrderRate();

        if ($invoice->getShippingAmount() > 0) {
            $quoteId = $invoice->getOrder()->getQuoteId();
            $quote = $this->quoteFactory->create();
            $quote->load($quoteId);

            $percentages = $this->getShippingTaxPercent($invoice->getOrderId());

            $shippingAddress = $quote->getShippingAddress();
            $rates = $shippingAddress->getAllShippingRates();

            $orderShippingMethod = $invoice->getOrder()->getShippingMethod();
            $shippingMethod = $this->shippingMethodStringToArray($orderShippingMethod);
            $shippingMethod = empty($shippingMethod) ? [$locationIds[0] => $orderShippingMethod] : $shippingMethod;

            foreach ($shippingMethod as $locationId => $code) {
                foreach ($rates as $rate) {
                    if ($rate->getLocationId() == $locationId && $code == $rate->getCode()) {
                        $vendorId = $rate->getVendorId();
                        if (!array_key_exists($vendorId, $total)) {
                            //item for this rate may already been removed.
                            continue;
                        }
                        $total[$vendorId]['shipping_amount'] += $rate->getPrice();
                        $total[$vendorId]['base_shipping_amount'] += ($rate->getPrice() / $baseToOrderRate);

                        foreach ($percentages as $taxCode => $percentage) {
                            $shippingTaxAmount = $this->getTaxAmount($rate->getPrice(), $percentage, $included);
                            $total[$vendorId]['shipping_tax'] += $shippingTaxAmount;
                            $total[$vendorId]['base_shipping_tax'] += ($shippingTaxAmount / $baseToOrderRate);
                        }
                    }
                }
            }
        }

        foreach ($total as $vendorId => $totalData) {
            if ($included) {
                $total[$vendorId]['shipping_incl_tax'] = $total[$vendorId]['shipping_amount'];
                $total[$vendorId]['base_shipping_incl_tax'] = $total[$vendorId]['base_shipping_amount'];
            } else {
                $total[$vendorId]['shipping_incl_tax'] =
                    $total[$vendorId]['shipping_amount'] + $total[$vendorId]['shipping_tax'];
                $total[$vendorId]['base_shipping_incl_tax'] =
                    $total[$vendorId]['base_shipping_amount'] + $total[$vendorId]['base_shipping_tax'];
            }
            $total[$vendorId]['grand_total'] = $total[$vendorId]['subtotal_incl_tax'] + $total[$vendorId]['shipping_incl_tax'];
            $total[$vendorId]['base_grand_total'] = $total[$vendorId]['base_subtotal_incl_tax'] + $total[$vendorId]['base_shipping_incl_tax'];
        }

        $totalData = [];
        foreach ($total as $vendorId => $totalArr) {
            $totalData[] = [
                'vendor_id'             => $vendorId,
                'invoice_id'            => $invoiceId,
                'subtotal'              => $totalArr['subtotal'],
                'base_subtotal'         => $totalArr['base_subtotal'],
                'subtotal_incl_tax'     => $totalArr['subtotal_incl_tax'],
                'base_subtotal_incl_tax'=> $totalArr['base_subtotal_incl_tax'],
                'tax_amount'            => $totalArr['tax_amount'],
                'base_tax_amount'       => $totalArr['base_tax_amount'],
                'discount_amount'       => $totalArr['discount_amount'],
                'base_discount_amount'  => $totalArr['base_discount_amount'],
                'shipping_amount'       => $totalArr['shipping_amount'],
                'base_shipping_amount'  => $totalArr['base_shipping_amount'],
                'shipping_incl_tax'     => $totalArr['shipping_incl_tax'],
                'base_shipping_incl_tax'=> $totalArr['base_shipping_incl_tax'],
                'shipping_tax'          => $totalArr['shipping_tax'],
                'base_shipping_tax'     => $totalArr['base_shipping_tax'],
                'grand_total'           => $totalArr['grand_total'],
                'base_grand_total'      => $totalArr['base_grand_total']
            ];
        }

        //save vendor order total into database
        $this->vendorResource->saveInvoiceTotal($totalData, [
            'subtotal',
            'base_subtotal',
            'subtotal_incl_tax',
            'base_subtotal_incl_tax',
            'tax_amount',
            'base_tax_amount',
            'discount_amount',
            'base_discount_amount',
            'shipping_amount',
            'base_shipping_amount',
            'shipping_incl_tax',
            'base_shipping_incl_tax',
            'shipping_tax',
            'base_shipping_tax',
            'grand_total',
            'base_grand_total'
        ]);

        return true;
    }

    protected function getTaxAmount($amount, $percent, $included)
    {
        if ($included) {
            return $amount * $percent / (100 + $percent);
        } else {
            return $amount * $percent * 0.01;
        }
    }

    protected function getShippingTaxPercent($orderId)
    {
        $orderTaxDetails = $this->orderTaxManagement->getOrderTaxDetails($orderId);
        $itemTaxDetails = $orderTaxDetails->getItems();
        $result = [];
        foreach ($itemTaxDetails as $itemTaxDetail) {
            //Aggregate taxable items associated with shipping
            if ($itemTaxDetail->getType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
                $itemAppliedTaxes = $itemTaxDetail->getAppliedTaxes();
                foreach ($itemAppliedTaxes as $itemAppliedTax) {
                    if (0 == $itemAppliedTax->getAmount() && 0 == $itemAppliedTax->getBaseAmount()) {
                        continue;
                    }
                    $result[$itemAppliedTax->getCode()] = $itemAppliedTax->getPercent();
                }
            }
        }
        return $result;
    }

    public function parseShippingMethod($method, $locationIds)
    {
        if (empty($method)) {
            return [];
        }

        $methodArr = $this->shippingMethodStringToArray($method);

        if (!empty($methodArr)) {
            return $methodArr;
        }

        $result = [];
        foreach ($locationIds as $locationId) {
            $result[$locationId] = $method;
        }
        return $result;
    }

    public function getAddressLocationIds($quoteAddress)
    {
        $locationIds = [];
        foreach ($quoteAddress->getAllItems() as $item) {
            $locationIds[] = $item->getLocationId();
        }
        return array_unique($locationIds);
    }

    public function getAllStores()
    {
        $result = [];
        $locations = $this->locationFactory->create()->getCollection();
        if (!($this->vendorConfig->isBindIncludeWarehouse())) {
            $locations->addFieldToFilter('is_warehouse', ['neq' => 1]);
        }
        $locations->joinVendorInfo();
        foreach ($locations as $location) {
            $vendorId = $location->getVendorId();
            if (!array_key_exists($vendorId, $result)) {
                $result[$vendorId] = $location;
            }
        }

        return $result;
    }

    public static function isValidAbn($abn)
    {
        $weights = array(10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19);
        // Strip non-numbers from the acn
        $abn = preg_replace('/[^0-9]/', '', $abn);
        // Check abn is 11 chars long
        if (strlen($abn) != 11) {
            return false;
        }
        // Subtract one from first digit
        $abn[0] = ((int) $abn[0] - 1);
        // Sum the products
        $sum = 0;
        foreach (str_split($abn) as $key => $digit) {
            $sum += ($digit * $weights[$key]);
        }
        if (($sum % 89) != 0) {
            return false;
        }
        return true;
    }

    public function getDistanceExpression($lat, $lng, $useHaversine = true)
    {
        $radLat = $lat * M_PI /180;
        $radLon = $lng * M_PI /180;

        if ($useHaversine) {
            return '('
                .'12742*ASIN('
                    .'SQRT( '
                        .'POWER( SIN( (' . $radLat . ' - rad_lat) * 0.5 ), 2)'
                        .'+'
                        . cos($radLat) . ' * cos_lat * POW( SIN( (' . $radLon . ' - rad_lon ) * 0.5), 2)'
                    .')'
                .')'
            .')'
            ;
        }

        return '('
            .'6371*ACOS(ROUND('
                . cos($radLat) . ' * cos_lat * COS( rad_lon - ' . $radLon . ')'
                .'+ ('
                . sin($radLat) . '* sin_lat), 8)'.
            ')'.
        ')'
        ;
    }

    public function isEnabledLocationFlat($storeId)
    {
        //TODO: load config by store id
        return true;
    }

    public function saveQuoteShipping($quoteId, $methods)
    {
        $this->vendorResource->saveQuoteShipping($quoteId, $methods);
    }

    public function getQuoteShipping($quoteId)
    {
        return $this->vendorResource->getQuoteShipping($quoteId);
    }

    public function getQuoteShippingInfo($quoteId)
    {
        return $this->vendorResource->getQuoteShippingInfo($quoteId);
    }

    public function getSourceCodeById($sourceId)
    {
        return $this->vSourceStockResource->getSourceCodeById($sourceId);
    }

    public function getVendorNameById($vendorId)
    {
        $name = $this->vendorResource->getVendorNameById($vendorId);
        return empty($name) ? 'Not Set' : $name;
    }

    public function getCanBindVendorTypeIds()
    {
        return $this->vendorConfig->getCanBindVendorTypeIds();
    }

    public function getInvoiceBy()
    {
        return $this->vendorConfig->getInvoiceBy();
    }

    public function getMoAbn()
    {
        return $this->vendorConfig->getMoAbn();
    }

    public function getMoName()
    {
        return $this->vendorConfig->getMoName();
    }

    public function getSupportLink()
    {
        return $this->vendorConfig->getSupportLink();
    }

    public function getUpdatedLabelProperties($defaultProperties)
    {
        if ($this->_request->getModuleName() === 'sales' && $this->_request->getControllerName() === 'order' && $this->_request->getActionName() === 'invoice') {
            // customer account dashboard
            return "colspan='5' class='mark'";
        } elseif ($this->_request->getModuleName() === 'sales' && $this->_request->getControllerName() === 'order_invoice' && $this->_request->getActionName() === 'email') {
            // invoice emails
            return "colspan='3' class='mark'";
        }
        return $defaultProperties;
    }

    public function getVendorAbnByItems($items)
    {
        $vendorIds = [];
        foreach ($items as $item) {
            $vendorIds[] = $item->getVendorId();
        }
        $vendorIds = array_unique($vendorIds);
        $collection = $this->getVendorsByIds($vendorIds);
        $collection->addAttributeToSelect('abn');
        $collection->load();
        $result = [];
        foreach ($vendorIds as $vendorId) {
            $vendor = $collection->getItemById($vendorId);
            $result[$vendorId] = empty($vendor) ? '' : $vendor->getData('abn');
        }
        return $result;
    }

    public function sendReviewProductEmailToMo($productName, $nickName, $summary, $review)
    {
        $templateId = 'review_product_email_template';
        $vars = [
            'mo_name'       =>  $this->scopeConfig->getValue(\Omnyfy\Vendor\Model\Config::XML_PATH_ADMIN_NAME),
            'product_name'  =>  $productName,
            'nick_name'     =>  $nickName,
            'summary'       =>  $summary,
            'review'        =>  $review
        ];
        $from = 'general';
        $to = [
            'email' => $this->scopeConfig->getValue(\Omnyfy\Vendor\Model\Config::XML_PATH_ADMIN_EMAIL),
            'name' => $this->scopeConfig->getValue(\Omnyfy\Vendor\Model\Config::XML_PATH_ADMIN_NAME)
        ];
        $storeId = $this->storeManager->getStore()->getId();
        $this->emailHelper->sendEmail($templateId, $vars, $from, $to, \Magento\Framework\App\Area::AREA_FRONTEND, $storeId);
    }

    public function sendReviewVendorEmailToMo($vendorName, $nickName, $summary, $review)
    {
        $templateId = 'review_vendor_email_to_mo_template';
        $vars = [
            'mo_name'       =>  $this->scopeConfig->getValue(\Omnyfy\Vendor\Model\Config::XML_PATH_ADMIN_NAME),
            'vendor_name'  =>  $vendorName,
            'nick_name'     =>  $nickName,
            'summary'       =>  $summary,
            'review'        =>  $review
        ];
        $from = 'general';
        $to = [
            'email' => $this->scopeConfig->getValue(\Omnyfy\Vendor\Model\Config::XML_PATH_ADMIN_EMAIL),
            'name' => $this->scopeConfig->getValue(\Omnyfy\Vendor\Model\Config::XML_PATH_ADMIN_NAME)
        ];
        $storeId = $this->storeManager->getStore()->getId();
        $this->emailHelper->sendEmail($templateId, $vars, $from, $to, \Magento\Framework\App\Area::AREA_FRONTEND, $storeId);
    }

    public function sendReviewVendorEmailToVendor($vendor, $nickName, $summary, $review)
    {
        $templateId = 'review_vendor_email_to_vendor_template';
        $vars = [
            'vendor_name'  =>  $vendor->getName(),
            'nick_name'     =>  $nickName,
            'summary'       =>  $summary,
            'review'        =>  $review
        ];
        $from = 'general';
        $to = [
            'email' => $vendor->getEmail(),
            'name' => $vendor->getName()
        ];
        $storeId = $this->storeManager->getStore()->getId();
        $this->emailHelper->sendEmail($templateId, $vars, $from, $to, \Magento\Framework\App\Area::AREA_FRONTEND, $storeId);
    }

    public function getVendorIdBySku($sku)
    {
        if (empty($sku)) {
            return;
        }

        $conn = $this->resource->getConnection();
        $selectProductId = $conn->select()->from('catalog_product_entity', 'entity_id')->where('sku = ?', $sku);
        $productId = $conn->fetchOne($selectProductId);
        $selectVendorId = $conn->select()->from('omnyfy_vendor_vendor_product', 'vendor_id')->where('product_id = ?', $productId);
        $rows = $conn->fetchOne($selectVendorId);

        return (!empty($rows)) ? $rows : null;
    }

    public function getVendorIdBySourceCode($sourceCode)
    {
        if (empty($sourceCode)) {
            return;
        }

        $collection = $this->vSourceStockCollectionFactory->create();
        $collection->addFieldToSelect('vendor_id');
        $collection->addFieldToFilter('main_table.source_code', ['eq' => $sourceCode]);
        if ($collection->getSize() > 0) {
            return $collection->getFirstItem()->getVendorId();
        }
        return '';
    }

    public function getProductIdBySku($sku)
    {
        if (empty($sku)) {
            return;
        }

        $conn = $this->resource->getConnection();
        $selectProductId = $conn->select()->from('catalog_product_entity', 'entity_id')->where('sku = ?', $sku);
        $productId = $conn->fetchOne($selectProductId);

        return $productId;
    }

    public function getSourceBySourceCode($sourceCode)
    {
        if (empty($sourceCode)) {
            return;
        }

        $sourceCollection = $this->sourceCollectionFactory->create();
        return $sourceCollection->getItemById($sourceCode);
    }

    public function getSourceStockIdBySourceCode($sourceCode)
    {
        if (empty($sourceCode)) {
            return;
        }
        return $this->vSourceStockResource->getIdsBySourceCode($sourceCode, true);
    }

    public function getStockIdByWebsiteCode($websiteCode)
    {
        if (empty($websiteCode)) {
            return;
        }

        $conn = $this->resource->getConnection();
        $query = $conn->select()->from('inventory_stock_sales_channel', 'stock_id')->where('code = ?', $websiteCode);
        return $conn->fetchOne($query);
    }

    public function vendorInvoiceTotalData($invoice)
    {
        $items = $invoice->getAllItems();
        $total = [];
        $locationIds = [];
        $arrayVendor = [];

        foreach ($invoice['items']->getItems() as $item) {
            if ($item->getQty() > 0) {
                $arrayVendor[$item->getVendorId()] = $item->getVendorId();
            }
        }

        foreach ($items as $item) {
            /** @var $item \Magento\Sales\Model\Order\Invoice\Item */
            $locationIds[] = $item->getLocationId();
            $vendorId = $item->getVendorId();
            if (!isset($total[$vendorId])) {
                $total[$vendorId] = [
                    'subtotal'                  => 0.0,
                    'base_subtotal'             => 0.0,
                    'subtotal_incl_tax'         => 0.0,
                    'base_subtotal_incl_tax'    => 0.0,
                    'tax_amount'                => 0.0,
                    'base_tax_amount'           => 0.0,
                    'shipping_amount'           => 0.0,
                    'base_shipping_amount'      => 0.0,
                    'shipping_incl_tax'         => 0.0,
                    'base_shipping_incl_tax'    => 0.0,
                    'discount_amount'           => 0.0,
                    'base_discount_amount'      => 0.0,
                    'shipping_tax_amount'          => 0.0,
                    'base_shipping_tax_amount'     => 0.0,
                    'grand_total'               => 0.0,
                    'base_grand_total'          => 0.0
                ];
            }
            $total[$vendorId]['subtotal'] += $item->getRowTotal();
            $total[$vendorId]['base_subtotal'] += $item->getBaseRowtotal();
            $total[$vendorId]['subtotal_incl_tax'] += $item->getRowTotalInclTax();
            $total[$vendorId]['base_subtotal_incl_tax'] += $item->getBaseRowTotalInclTax();
            $total[$vendorId]['tax_amount'] += $item->getTaxAmount();
            $total[$vendorId]['base_tax_amount'] += $item->getBaseTaxAmount();
            $total[$vendorId]['discount_amount'] += $item->getDiscountAmount();
            $total[$vendorId]['base_discount_amount'] += $item->getBaseDiscountAmount();
        }

        $quoteId = $invoice->getOrder()->getQuoteId();
        $included = $this->isOrderShippingIncludedTax($invoice->getShippingIncTax(), $quoteId);
        $baseToOrderRate = $invoice->getBaseToOrderRate();

        if ($invoice->getOrder()->getShippingAmount() > 0) {
            $quote = $this->quoteFactory->create();
            $quote->load($quoteId);

            $percentages = $this->getShippingTaxPercent($invoice->getOrderId());

            $shippingAddress = $quote->getShippingAddress();
            $rates = $shippingAddress->getAllShippingRates();

            $orderShippingMethod = $invoice->getOrder()->getShippingMethod();
            $shippingMethod = $this->shippingMethodStringToArray($orderShippingMethod);
            $shippingMethod = empty($shippingMethod) ? [$locationIds[0] => $orderShippingMethod] : $shippingMethod;

            foreach ($shippingMethod as $locationId => $code) {
                foreach ($rates as $rate) {
                    if ($rate->getLocationId() == $locationId && $code == $rate->getCode()) {
                        $vendorId = $rate->getVendorId();
                        if (!array_key_exists($vendorId, $total)) {
                            //item for this rate may already been removed.
                            continue;
                        }
                        $skip = false;

                        // Check if previous invoice already calculate shipping amount for current method
                        foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
                            if ((double)$previousInvoice->getShippingAmount() && !$previousInvoice->isCanceled() && $previousInvoice->getId()) {
                                $allPreviousInvoiceItem = $previousInvoice->getAllItems();
                                if (!empty($allPreviousInvoiceItem)) {
                                    foreach ($allPreviousInvoiceItem as $previousItem) {
                                        if (in_array($previousItem->getVendorId(), $arrayVendor)) {
                                            $skip = true;
                                            unset($arrayVendor[$previousItem->getVendorId()]);
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        if ($skip) {
                            continue;
                        }
                        if (empty($arrayVendor[$rate->getVendorId()])) {
                            continue;
                        }
                        foreach ($percentages as $taxCode => $percentage) {
                            $shippingTaxAmount = $this->getTaxAmount($rate->getPrice(), $percentage, $included);
                            $total[$vendorId]['shipping_tax_amount'] += $shippingTaxAmount;
                            $total[$vendorId]['base_shipping_tax_amount'] += ($shippingTaxAmount / $baseToOrderRate);
                            $total[$vendorId]['tax_amount'] += $shippingTaxAmount ;
                            $total[$vendorId]['base_tax_amount'] += ($shippingTaxAmount / $baseToOrderRate);
                        }
                        if (!$included) {
                            $total[$vendorId]['shipping_amount'] += $rate->getPrice();
                            $total[$vendorId]['base_shipping_amount'] += ($rate->getPrice() / $baseToOrderRate);
                        } else {
                            $total[$vendorId]['shipping_amount'] += ($rate->getPrice() + $total[$vendorId]['shipping_tax_amount']);
                            $total[$vendorId]['base_shipping_amount'] += ($rate->getPrice() / $baseToOrderRate + $total[$vendorId]['base_shipping_tax_amount']);
                        }

                        unset($arrayVendor[$rate->getVendorId()]);
                    }
                }
            }
        }

        foreach ($total as $vendorId => $totalData) {
            if ($included) {
                $total[$vendorId]['shipping_incl_tax'] = $total[$vendorId]['shipping_amount'];
                $total[$vendorId]['base_shipping_incl_tax'] = $total[$vendorId]['base_shipping_amount'];
            } else {
                $total[$vendorId]['shipping_incl_tax'] =
                    $total[$vendorId]['shipping_amount'] + $total[$vendorId]['shipping_tax_amount'];
                $total[$vendorId]['base_shipping_incl_tax'] =
                    $total[$vendorId]['base_shipping_amount'] + $total[$vendorId]['base_shipping_tax_amount'];
            }
            $total[$vendorId]['grand_total'] = $total[$vendorId]['subtotal_incl_tax'] + $total[$vendorId]['shipping_incl_tax'];
            $total[$vendorId]['base_grand_total'] = $total[$vendorId]['base_subtotal_incl_tax'] + $total[$vendorId]['base_shipping_incl_tax'];
        }

        $totalData = [];
        foreach ($total as $vendorId => $totalArr) {
            $totalData[] = [
                'vendor_id'             => $vendorId,
                'invoice_id'            => $invoice->getId(),
                'subtotal'              => $totalArr['subtotal'],
                'base_subtotal'         => $totalArr['base_subtotal'],
                'subtotal_incl_tax'     => $totalArr['subtotal_incl_tax'],
                'base_subtotal_incl_tax'=> $totalArr['base_subtotal_incl_tax'],
                'tax_amount'            => $totalArr['tax_amount'],
                'base_tax_amount'       => $totalArr['base_tax_amount'],
                'discount_amount'       => $totalArr['discount_amount'],
                'base_discount_amount'  => $totalArr['base_discount_amount'],
                'shipping_amount'       => $totalArr['shipping_amount'],
                'base_shipping_amount'  => $totalArr['base_shipping_amount'],
                'shipping_incl_tax'     => $totalArr['shipping_incl_tax'],
                'base_shipping_incl_tax'=> $totalArr['base_shipping_incl_tax'],
                'shipping_tax_amount'          => $totalArr['shipping_tax_amount'],
                'base_shipping_tax_amount'     => $totalArr['base_shipping_tax_amount'],
                'grand_total'           => $totalArr['grand_total'],
                'base_grand_total'      => $totalArr['base_grand_total']
            ];
        }
        return $totalData;
    }

    public function getShippingFeeFromQuote($quoteId)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('omnyfy_vendor_quote_shipping');
        $sql = 'SELECT amount FROM ' . $tableName . ' WHERE `quote_id` =  ' . $quoteId ;
        return $connection->fetchAll($sql);
    }

    public function isOrderShippingIncludedTax($shippingIncTax, $quoteId)
    {
        $quoteShipping = $this->getShippingFeeFromQuote($quoteId);
        $total = 0;
        foreach ($quoteShipping as $rate) {
            $total += $rate['amount'];
        }
        return $shippingIncTax == $total;
    }

    public function getShippingData($order, $invoice)
    {
        if (empty($this->shippingData)) {
            $quoteId = $order->getQuoteId();
            $quote = $this->quoteFactory->create()->load($quoteId);
            $shippingAddress = $quote->getShippingAddress();
            $shippingData = [];
            $rates = $shippingAddress->getAllShippingRates();

            foreach ($invoice->getItems() as $item) {
                $locationIds[] = $item->getLocationId();
            }


            // Get shipping method from order

            $shippingMethod = $this->shippingMethodStringToArray($order->getShippingMethod());

            $shippingMethod = empty($shippingMethod) ? [$locationIds[0] => $order->getShippingMethod()] : $shippingMethod;
            $shippingTitle = '';
            $shippingAmount = 0;
            $vendorInfo = $this->backendSession->create()->getVendorInfo();
            foreach ($rates as $rate) {
                if (isset($vendorInfo) && $rate->getVendorId() != $vendorInfo['vendor_id']) {
                    continue;
                }
                $locationId = $rate->getLocationId();
                if (!array_key_exists($locationId, $shippingMethod)) {
                    continue;
                }
                if ($shippingMethod[$locationId] != $rate->getCode()) {
                    continue;
                }

                $shippingTitle .= $rate->getCarrierTitle() . " - " . $rate->getMethodTitle()."\n";
                $shippingAmount += $rate->getPrice();
            }
            $shippingData['title'] = $shippingTitle;
            $shippingData['amount'] = $shippingAmount;
            $shippingData['vendor_info'] = $vendorInfo;
            $this->shippingData = $shippingData;
        }
        return $this->shippingData;
    }

    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
