<?php

namespace Omnyfy\Mcm\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;
use Omnyfy\Mcm\Model\ShippingCalculationFactory;
use Omnyfy\Mcm\Model\ResourceModel\VendorOrder\CollectionFactory as VendorOrderCollectionFactory;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout;

class Payout extends AbstractHelper {

    protected $_storeManager;

    protected $orderTaxManagement;

    protected $priceCurrency;

    protected $feesManagementResource;

    protected $_shippingCalculationFactory;

    protected $vendorOrderCollectionFactory;

    protected $mcmHelper;

    protected $resourceConnection;

    protected $orderRepository;

    protected $_items = [];

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    protected $_vendorPayout;

    public function __construct(
        Context $context,
        \Magento\Tax\Api\OrderTaxManagementInterface $orderTaxManagement,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrencyInterface,
        \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $feesManagementResource,
        \Omnyfy\Mcm\Model\ShippingCalculationFactory  $shippingCalculationFactory,
        VendorOrderCollectionFactory $vendorOrderCollectionFactory,
        \Omnyfy\Mcm\Helper\Data $mcmHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        VendorRepositoryInterface $vendorRepository,
        VendorPayout $vendorPayout
    ) {
        $this->_storeManager = $storeManager;
        $this->orderTaxManagement = $orderTaxManagement;
        $this->priceCurrency = $priceCurrencyInterface;
        $this->feesManagementResource = $feesManagementResource;
        $this->_shippingCalculationFactory = $shippingCalculationFactory;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
        $this->mcmHelper = $mcmHelper;
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->vendorRepository = $vendorRepository;
        $this->_vendorPayout = $vendorPayout;
        parent::__construct($context);
    }

    // @TODO - placeholder to vendor payout value
    public function getVendorPayoutValue($vendorId)
    {

    }

    // @TODO - placeholder to check
    public function doesShippingCalculationExist($vendorOrder)
    {
        // Get order shipments that are ship by type 2 which is vendor
        // ship_by_type = 1 (Marketplace Owner)
        // ship_by_type = 2 (Vendor)
        $vendorShipments = $this->_shippingCalculationFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $vendorOrder->getOrderId())
            ->addFieldToFilter('vendor_id', $vendorOrder->getVendorId())
            ->addFieldToFilter('ship_by_type', '2');

        if ($vendorShipments->getSize() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getOrderPayoutShippingAmount($vendorOrder)
    {
        // Get order shipments that are ship by type 2 which is vendor
        // ship_by_type = 1 (Marketplace Owner)
        // ship_by_type = 2 (Vendor)
        $vendorShipments = $this->_shippingCalculationFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $vendorOrder->getOrderId())
            ->addFieldToFilter('vendor_id', $vendorOrder->getVendorId())
            ->addFieldToFilter('ship_by_type', '2');

        $orderShipmentTotal = 0;
        if ($vendorShipments->getSize() > 0) {
            foreach($vendorShipments as $vendorShipment) {
                $orderShipmentTotal += $vendorShipment->getCustomerPaid();
            }
        }

        return $orderShipmentTotal;
    }

    public function getReadyToPayoutVendorOrderCollection($vendorId)
    {
        $vendorOrderCollection = $this->vendorOrderCollectionFactory->create();
        $vendorOrderCollection = $vendorOrderCollection->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('payout_status', 0)
            ->addFieldToFilter('payout_action', 1);

        return $vendorOrderCollection;
    }

    public function getPendingOrderVendorCollection($vendorId)
    {
        $vendorOrderCollection = $this->vendorOrderCollectionFactory->create();
        $vendorOrderCollection = $vendorOrderCollection->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('payout_status', 0)
            ->addFieldToFilter('payout_action', 0);

        return $vendorOrderCollection;
    }

    public function getPayoutAmount($vendorOrderTotalIncTax, $vendorOrderFeeTotalIncTax, $vendorOrderMcm)
    {
        // Wholesale Vendor
        if ($this->getVendorPayoutBasisType($vendorOrderMcm->getVendorId()) == PayoutBasisType::WHOLESALE_VENDOR_VALUE) {
            $payoutAmount = $this->_vendorPayout->getPayoutTotalWholesaleVendor($vendorOrderMcm->getOrderId(), $vendorOrderMcm->getVendorId(), $vendorOrderMcm->getId());
        } else {
            // Commission Vendor
            // This needs to take into account the shipping fees
            $payoutAmount = $vendorOrderTotalIncTax - $vendorOrderFeeTotalIncTax - $vendorOrderMcm->getBaseRefundAmount();
        }

        $payoutAmount = $this->getPayoutShippingValue($vendorOrderMcm, $payoutAmount);

        return $payoutAmount;
    }

    // Check whether or not the shipping should be paid to vendor in payout
    public function getPayoutShippingValue($vendorOrderMcm, $payoutAmount)
    {
        // @TODO - add into method as both commission and wholesale need to check for shipping
        // If MCM is set not to manage shipping fees, if ship by type is also disabled, pay out to vendor
        if (!$this->mcmHelper->getShipByTypeConfiguration()) {
            if ($this->mcmHelper->manageShippingFees()) {
                $payoutAmount -= $vendorOrderMcm->getShippingInclTax();
            }
        }

        return $payoutAmount;
    }
    public function getOrderShippingFee($order)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('omnyfy_vendor_quote_shipping'); //gives table name with prefix

        $vendorOrder = $this->orderRepository->get($order->getOrderId());
        $quoteId = $vendorOrder->getQuoteId();

        //Select Data from table
        $sql = 'SELECT amount FROM ' . $tableName . ' WHERE `quote_id` =  ' . $quoteId . ' AND vendor_id = ' . $order->getVendorId();
        $result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.

        return $result;

    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getVendorPayoutBasisType($vendorId){
        $vendor = $this->vendorRepository->getById($vendorId);
        return $vendor->getPayoutBasisType();
    }

    public function totalEarning($vendorOrderCollection) {
        $total = 0;
        foreach ($vendorOrderCollection as $order) {
            $payoutAmount = $this->_vendorPayout->getPayoutAmount($order->getVendorId(), $order->getOrderId()) ?? 0;
            $total += $payoutAmount;
        }
        return $total;
    }
}
