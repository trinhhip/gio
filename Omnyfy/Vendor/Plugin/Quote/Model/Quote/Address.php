<?php
/**
 * Project: Omnyfy Multi Vendor.
 * User: jing
 * Date: 20/4/17
 * Time: 11:57 AM
 */

namespace Omnyfy\Vendor\Plugin\Quote\Model\Quote;

use Magento\Framework\Exception\LocalizedException;

class Address
{
    protected $_addressRateFactory;

    protected $_rateCollector;

    protected $_rateRequestFactory;

    protected $helper;

    protected $_locationResource;

    protected $shippingHelper;

    protected $vendorSourceStock;

    protected $source;

    protected $vendorSourceStockResource;

    protected $vSourceStockCollectionFactory;

    protected $sourceRepository;

    protected $getDistanceFromSourceToAddress;

    protected $inventoryAddressFactory;

    protected $stockManager;

    protected $inventoryResource;

    protected $getStockItemConfiguration;

    protected $isProductSalableForRequestedQty;

    protected $productRepository;

    protected $getAssignedStockIdForWebsite;

    protected $isProductSaleableForSource;

    public function __construct(
        \Magento\Quote\Model\Quote\Address\RateFactory $addressRateFactory,
        \Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory $rateCollector,
        \Magento\Quote\Model\Quote\Address\RateRequestFactory $rateRequestFactory,
        \Omnyfy\Vendor\Helper\Data $helper,
        \Omnyfy\Vendor\Model\Resource\Location $_locationResource,
        \Omnyfy\Vendor\Helper\Shipping $shippingHelper,
        \Omnyfy\Vendor\Model\VendorSourceStock $vendorSourceStock,
        \Magento\Inventory\Model\Source $source,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vendorSourceStockResource,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock\CollectionFactory $vSourceStockCollectionFactory,
        \Magento\Inventory\Model\SourceRepository $sourceRepository,
        \Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider\GetDistanceFromSourceToAddress $getDistanceFromSourceToAddress,
        \Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory $inventoryAddressFactory,
        \Magento\Store\Model\StoreManagerInterface $stockManager,
        \Omnyfy\Vendor\Model\Resource\Inventory $inventoryResource,
        \Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface $getStockItemConfiguration,
        \Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite,
        \Omnyfy\Vendor\Model\IsProductSaleableForSource $isProductSaleableForSource
    )
    {
        $this->_addressRateFactory = $addressRateFactory;
        $this->_rateCollector = $rateCollector;
        $this->_rateRequestFactory = $rateRequestFactory;
        $this->helper = $helper;
        $this->_locationResource = $_locationResource;
        $this->shippingHelper = $shippingHelper;
        $this->vendorSourceStock = $vendorSourceStock;
        $this->source = $source;
        $this->vendorSourceStockResource = $vendorSourceStockResource;
        $this->vSourceStockCollectionFactory = $vSourceStockCollectionFactory;
        $this->sourceRepository = $sourceRepository;
        $this->getDistanceFromSourceToAddress = $getDistanceFromSourceToAddress;
        $this->inventoryAddressFactory = $inventoryAddressFactory;
        $this->stockManager = $stockManager;
        $this->inventoryResource = $inventoryResource;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
        $this->productRepository = $productRepository;
        $this->getAssignedStockIdForWebsite = $getAssignedStockIdForWebsite;
        $this->isProductSaleableForSource = $isProductSaleableForSource;
    }

    public function aroundRequestShippingRates(
        \Magento\Quote\Model\Quote\Address $subject,
        callable $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item = null
    )
    {
        //request shipping rates for single item
        if (!is_null($item)) {
            return $proceed($item);
        }

        $isPos = false;
        //if there's location id in ext_shipping_info, we know it's pos
        $extInfo = $subject->getQuote()->getExtShippingInfo();
        $extInfo = empty($extInfo) ? [] : json_decode($extInfo, true);
        if (!empty($extInfo) && isset($extInfo['location_id'])) {
            $isPos = true;
        }

        if ($subject->getQuote()->getIsMultiShipping()) {
            //return $proceed($item);
        }

        //group items by location id
        $grouped = [];

        $allItems = $subject->getAllItems();

        $isMvcpItem = $this->checkIfMvcpProductPresent($allItems);
        $mvcpItemLocationId = $this->getMvcpLastOptionLocation($allItems);

        $shippingConfiguration = $this->shippingHelper->getCalculateShippingBy();
        if ($shippingConfiguration == 'overall_cart') {
            $shippingPickupLocation = $this->shippingHelper->getShippingConfiguration('overall_pickup_location');
        }

        foreach($allItems as $item) {
            $requestQty = $item->getData('qty');
            $address = $item->getAddress();
            $websiteId = $this->stockManager->getWebsite()->getId();
            $websiteCode = $this->stockManager->getWebsite()->getCode();
            $sku = $this->productRepository->getById($item->getProductId())->getSku();
            $vendorId = $item->getVendorId();
            $qtys = $this->inventoryResource->loadInventoryGroupedByLocation($item->getProductId(), $websiteId, $vendorId);
            $stockId = $this->getAssignedStockIdForWebsite->execute($websiteCode);
            $sourceStockIds = [];
            $distanceBySourceCode = [];
            $sourceCodeBySourceStockId = [];
            if (is_countable($qtys) && count($qtys) > 1) {
                $addressData = [
                    'country' => $address->getCountry(),
                    'postcode' => $address->getPostcode(),
                    'street' => $address->getStreet()[0],
                    'region' => $address->getRegion(),
                    'city' => $address->getCity()
                ];
                if (!empty($addressData['country']) && !empty($addressData['postcode']) && !empty($addressData['street']) && !empty($addressData['region']) && !empty($addressData['city'])) {
                    $inventoryAddress = $this->inventoryAddressFactory->create($addressData);
                    foreach ($qtys as $sourceStockId => $qty) {
                        $sourceStockIds[] = $sourceStockId;
                    }
                    $sourceStockCollection = $this->vSourceStockCollectionFactory->create()->addFieldToFilter('id', ['in' => $sourceStockIds]);
                    foreach ($qtys as $sourceStockId => $quantity) {
                        $sourceCode = $sourceStockCollection->getItemById($sourceStockId)->getSourceCode();
                        $sourceCodeBySourceStockId[$sourceStockId] = $sourceCode;
                        $source = $this->sourceRepository->get($sourceCode);

                        try {
                            $distanceBySourceCode[$sourceStockId] = $this->getDistanceFromSourceToAddress->execute($source, $inventoryAddress);
                        } catch (LocalizedException $e) {
                        }
                    }
                    asort($distanceBySourceCode);
                    if (!empty($distanceBySourceCode)) {
                        foreach ($distanceBySourceCode as $id => $distance) {
                            if (!$this->getStockItemConfiguration->execute($sku, $stockId)->isManageStock()) {
                                $item->setLocationId($id);
                                $item->setSourceStockId($id);
                                $item->save();
                                break;
                            }
                            if ($this->isProductSaleableForSource->execute($sku, $stockId, $sourceCodeBySourceStockId[$id], $requestQty)) {
                                $item->setLocationId($id);
                                $item->setSourceStockId($id);
                                $item->save();
                                break;
                            }
                        }
                    } else {
                        foreach ($qtys as $id => $qty) {
                            if (!$this->getStockItemConfiguration->execute($sku, $stockId)->isManageStock()) {
                                $item->setLocationId($id);
                                $item->setSourceStockId($id);
                                $item->save();
                                break;
                            }
                            if ($this->isProductSaleableForSource->execute($sku, $stockId, $sourceCodeBySourceStockId[$id], $requestQty)) {
                                $item->setLocationId($id);
                                $item->setSourceStockId($id);
                                $item->save();
                                break;
                            }
                        }
                    }
                }
            }

            if ($isMvcpItem && $mvcpItemLocationId != '') {
                $sourceStockId = $mvcpItemLocationId;
            } else {
                $sourceStockId = $item->getSourceStockId();
            }

            if (empty($sourceStockId) && $item instanceof \Magento\Quote\Model\Quote\Address\Item ) {
                $sourceStockId = $item->getQuoteItem()->$sourceStockId();
            }

            if (empty($sourceStockId)) {
                //TODO: throw exception
                return false;
            }

            // if set to overall cart, all items will be coming from the pickup address
            if ($shippingConfiguration == 'overall_cart' && !empty($shippingPickupLocation)) {
                $sourceStockId = $shippingPickupLocation;
            }

            //booking product no shipping needed
            if (!empty($item->getBookingId())) {
                continue;
            }

            if (!isset($grouped[$sourceStockId])) {
                $grouped[$sourceStockId] = [
                    'items' => [],
                    'package_value' => 0,
                    'package_with_discount' => 0,
                    'package_weight' => 0,
                    'package_qty' => 0,
                    'package_physical_value' => 0
                ];
            }
            //Keep all items in array, but only calculate parent items for package info.
            $grouped[$sourceStockId]['items'][] = $item;

            //Do not ignore virtual product
            if ( $item->getParentItem()) {
                continue;
            }

            $packageValue = $item->getBaseRowTotal();
            $shipValue = $item->getShipValue();
            $packageValue = empty($shipValue) ? $packageValue : $shipValue;
            $grouped[$sourceStockId]['package_value'] += $packageValue;
            $grouped[$sourceStockId]['package_with_discount'] += $packageValue - $item->getBaseDiscountAmount();
            $grouped[$sourceStockId]['package_weight'] += $item->getRowWeight();
            $grouped[$sourceStockId]['package_qty'] += $item->getQty();
            $grouped[$sourceStockId]['package_physical_value'] += $item->getBaseRowTotal();
        }

        if (empty($grouped)) {
            //TODO: throw exception
            return false;
        }

        //Even there's only one group, we need to override the origin in request as location info in our database.
        $sourceStockIds = array_keys($grouped);

//        $locationId2VendorId = $this->_locationResource->getVendorIdsByLocationIds($locationIds);

        $sourceStocId2VendorId = $this->vendorSourceStockResource->getVendorIdsBySourceStockIds($sourceStockIds);

        //load current shipping method settings
        $shippingMethod = $subject->getShippingMethod();

        $methods = $this->helper->shippingMethodStringToArray($shippingMethod);

        // if there's only one method
        if (empty($methods) && (1 == count($grouped))) {
            $sourceStockId = $sourceStockIds[0];
            $methods = [$sourceStockId => $shippingMethod];
        }

        // load all locations by location ids
//        $locations = $this->helper->getLocationsByIds($locationIds);

        $allFound = true;

        $shippingAmount = 0;
        foreach($grouped as $sourceStockId => $data) {
            /** @var $request \Magento\Quote\Model\Quote\Address\RateRequest */
            $request = $this->_rateRequestFactory->create();
            $request->setAllItems($data['items']);
            $request->setDestCountryId($subject->getCountryId());
            $request->setDestRegionId($subject->getRegionId());
            $request->setDestRegionCode($subject->getRegionCode());
            $request->setDestStreet($subject->getStreetFull());
            $request->setDestCity($subject->getCity());
            $request->setDestPostcode($subject->getPostcode());
            $request->setDestRegionId($subject->getRegionId());
            $request->setDestRegionCode($subject->getRegionCode());
            $request->setDestStreet($subject->getStreetFull());
            $request->setDestCity($subject->getCity());
            $request->setDestPostcode($subject->getPostcode());
            $request->setPackageValue($data['package_value']);
            $request->setPackageValueWithDiscount($data['package_with_discount']);
            $request->setPackageWeight($data['package_weight']);
            $request->setPackageQty($data['package_qty']);

            if ($isPos) {
                $request->setIsPos('1');
            }

            /**
             * Need for shipping methods that use insurance based on price of physical products
             */
            $request->setPackagePhysicalValue($data['package_physical_value']);
            $request->setFreeMethodWeight($subject->getFreeMethodWeight());
            /**
             * Store and website identifiers need specify from quote
             */
            $request->setStoreId($subject->getQuote()->getStore()->getId());
            $request->setWebsiteId($subject->getQuote()->getStore()->getWebsiteId());
            $request->setFreeShipping($subject->getFreeShipping());

            /**
             * Currencies need to convert in free shipping
             */
            $request->setBaseCurrency($subject->getQuote()->getStore()->getBaseCurrency());
            $request->setPackageCurrency($subject->getQuote()->getStore()->getCurrentCurrency());
            $request->setLimitCarrier($subject->getLimitCarrier());
            $request->setBaseSubtotalInclTax($subject->getBaseSubtotalTotalInclTax());

            if($subject->getLimitCarrier()) {
                $limitCarrier = $this->helper->getLimitCarrier($methods[$sourceStockId]);
                $request->setLimitCarrier($limitCarrier);
            }

            $request->setDestFirstname($subject->getFirstname());
            $request->setDestLastName($subject->getLastname());
            $request->setAddressId($subject->getId());

            $sourceStock = $this->vendorSourceStock->load($sourceStockId);
            $souce = $this->source->load($sourceStock->getSourceCode());
            //$request->setOrig(true) and set origin based on location id
//            $location = $locations->getItemById($locationId);
//            if (!empty($location)) {
//                $request->setLocationId($locationId);
//
//                $request
//                    ->setOrigAddress($location->getAddress())
//                    ->setOrigCountryId($location->getCountry())
//                    ->setOrigRegionId($location->getRegionId())
//                    ->setOrigState($location->getRegion())
//                    ->setOrigCity($location->getSuburb())
//                    ->setOrigPostcode($location->getPostcode())
//                $request->setOrig(true);
//            }
            if (!empty($souce)) {
                $request->setSourceStockId($sourceStockId);

                $request
                    ->setOrigAddress($souce->getStreet())
                    ->setOrigCountryId($souce->getCountryId())
                    ->setOrigRegionId($souce->getRegionId())
                    ->setOrigState($souce->getRegion())
                    ->setOrigCity($souce->getCity())
                    ->setOrigPostcode($souce->getPostcode())
                ;
                $request->setOrig(true);
            }
            if(!empty($methods[$sourceStockId]) && $methods[$sourceStockId] == "quotation_quotation"){
                $request->setRfqFixedPrice($subject->getShippingAmount());
            }
            $result = $this->_rateCollector->create()->collectRates($request)->getResult();

            $found = false;
            if ($result) {
                $shippingRates = $result->getAllRates();

                foreach ($shippingRates as $shippingRate) {
                    $rate = $this->_addressRateFactory->create()->importShippingRate($shippingRate);
                    $rate->setData('location_id', $sourceStockId);
                    $rate->setData('source_stock_id', $sourceStockId);
                    if (array_key_exists($sourceStockId, $sourceStocId2VendorId)) {
                        $rate->setData('vendor_id', $sourceStocId2VendorId[$sourceStockId]);
                    }
                    if ($shippingRate->hasData('additional_data')) {
                        $rate->setData('additional_data', $shippingRate->getAdditionalData());
                    }

                    $subject->addShippingRate($rate);

                    if (isset($methods[$sourceStockId]) && $methods[$sourceStockId] == $rate->getCode()) {
                        $shippingAmount += $rate->getPrice();
                        $found = true;
                    }
                }
            }

            if (!$found) {
                $allFound = false;
            }
        }

        if ($allFound) {
            /**
             * possible bug: this should be setBaseShippingAmount(),
             * see \Magento\Quote\Model\Quote\Address\Total\Shipping::collect()
             * where this value is set again from the current specified rate price
             * (looks like a workaround for this bug)
             */
            $subject->setShippingAmount($shippingAmount);
        }
        return $allFound;
    }

    public function aroundGetShippingRateByCode($subject, callable $process, $shippingMethod)
    {
        if ('{' !== substr($shippingMethod, 0, 1)) {
            return $process($shippingMethod);
        }

        $methods = $this->helper->shippingMethodStringToArray($shippingMethod);
        foreach($subject->getShippingRatesCollection() as $rate) {
            foreach($methods as $locationId => $code) {
                if ($rate->getCode() == $code) {
                    return $rate;
                }
            }
        }

        return false;
    }

    public function checkIfMvcpProductPresent($allItems)
    {
        // Loop through items to check if there is an mvcp product
        foreach($allItems as $item) {
            if ($item->getProduct()->getTypeId() == 'mvcp') {
                return true;
            }
        }

        return false;
    }

    public function getMvcpLastOptionLocation($allItems)
    {
        $mvcpItemLocationId = '';
        $mvcpSortOrder = '';
        foreach($allItems as $item) {
            if ($mvcpItemLocationId == '' && $mvcpSortOrder == '') {
                $mvcpItemLocationId = $item->getLocationId();
                $mvcpSortOrder = $item->getSortOrder();
            } elseif ($item->getSortOrder() > $mvcpSortOrder) {
                $mvcpSortOrder = $item->getSortOrder();
                $mvcpItemLocationId = $item->getLocationId();
            }
        }

        return $mvcpItemLocationId;
    }
}
