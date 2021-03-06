<?php

namespace Omnyfy\Mcm\Model\ResourceModel;

use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;
use Omnyfy\Mcm\Model\ResourceModel\VendorOrder\CollectionFactory as VendorOrderCollectionFactory;

class VendorPayout extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements \Omnyfy\Mcm\Api\VendorPayoutInterface
{

    protected $connection;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    protected $mcmHelper;

    protected $shippingHelper;

    protected $locationHelper;

    /**
     * @var VendorOrderFactory
     */
    protected $vendorOrderResourceFactory;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    protected $transactionRebateRepository;

    protected $vendorOrderCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Omnyfy\Mcm\Helper\Data $mcmHelper,
        \Omnyfy\Vendor\Helper\Shipping $shippingHelper,
        \Omnyfy\Vendor\Helper\Location $locationHelper,
        \Omnyfy\Mcm\Model\ResourceModel\VendorOrderFactory $vendorOrderResourceFactory,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Omnyfy\RebateCore\Model\Repository\TransactionRebateRepository $transactionRebateRepository,
        \Omnyfy\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        VendorOrderCollectionFactory $vendorOrderCollectionFactory,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
        $this->dateTime = $dateTime;
        $this->orderRepository = $orderRepository;
        $this->mcmHelper = $mcmHelper;
        $this->shippingHelper = $shippingHelper;
        $this->locationHelper = $locationHelper;
        $this->vendorOrderResourceFactory = $vendorOrderResourceFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->transactionRebateRepository = $transactionRebateRepository;
        $this->vendorRepository = $vendorRepository;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
        //$this->connection = $this->getConnection();;
    }

    /**
     * Define main table
     */
    protected function _construct() {
        $this->_init('omnyfy_mcm_vendor_payout', 'payout_id');
    }

    /**
     * Process template data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object) {
        $gmtDate = $this->_date->gmtDate();

        if ($object->isObjectNew() && !$object->getCreatedAt()) {
            $object->setCreatedAt($gmtDate);
        }

        $object->setUpdatedAt($gmtDate);

        return parent::_beforeSave($object);
    }

    /**
     * Process template data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(
        \Magento\Framework\Model\AbstractModel $object
    ) {
        return parent::_beforeDelete($object);
    }

    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object) {
        return parent::_afterLoad($object);
    }

    /**
     * @param $orderId
     * @param $vendorId
     */
    public function getVendorOrderTotals($orderId, $vendorId) {
        $adapter = $this->getConnection();
        $table = $this->getTable('omnyfy_mcm_vendor_order_item');
        $select = $adapter->select()->from(
            $table, ['vendor_id', 'order_id', 'seller_fee' => 'SUM(seller_fee)', 'seller_fee_tax' => 'SUM(seller_fee_tax)', 'category_fee' => 'SUM(category_fee)', 'category_fee_tax' => 'SUM(category_fee_tax)', 'row_total' => 'SUM(row_total)', 'tax_amount' => 'SUM(tax_amount)', 'row_total_incl_tax' => 'SUM(row_total_incl_tax)']
        )->where(
            "vendor_id = ?", (int) $vendorId
        )->where(
            "order_id = ?", (int) $orderId
        );
        $result = $adapter->fetchRow($select);
        if (!empty($result)) {
            return $result;
        }
        return;
    }

    public function getVendorOrder($orderId, $vendorId){
        $adapter = $this->getConnection();
        $table = $this->getTable('omnyfy_mcm_vendor_order');
        $select = $adapter->select()->from(
            $table, [
                'order_id', 'vendor_id', 'payout_amount','base_subtotal_incl_tax',
                'total_category_fee_incl_tax' => 'SUM(total_category_fee + total_category_fee_tax)',
                'total_seller_fee_incl_tax' => 'SUM(total_seller_fee + total_seller_fee_tax)',
                'total_disbursement_fee_incl_tax' => 'SUM(disbursement_fee + disbursement_fee_tax)',
                'shipping_incl_tax' => 'shipping_incl_tax'
        ])->where(
            "vendor_id = ?", (int) $vendorId
        )->where(
            "order_id = ?", (int) $orderId
        );
        return $adapter->fetchRow($select);
    }

    public function getPayoutBalanceTotal($vendorId = '')
    {
        $totalPayoutsReady = 0;
        $totalRebates = 0;

        if ($vendorId != '') {
            $ordersPendingPayout = $this->getReadyToPayoutVendorOrderCollection($vendorId);

            if ($ordersPendingPayout->getSize() > 0) {
                foreach ($ordersPendingPayout as $orderPendingPayout) {
                    $totalPayoutsReady += $orderPendingPayout->getPayoutAmount();

                    $vendorRebatesTotal = $this->rebateOrder($vendorId, $orderPendingPayout->getOrderId());
                    if (!empty($vendorRebatesTotal)) {
                        $totalRebates += $vendorRebatesTotal;
                    }
                }
            }
        }

        // The total pending order total value
        $vendorTotalPending = $totalPayoutsReady - $totalRebates;

        return $vendorTotalPending;
    }

    public function getPendingBalanceOwingTotal($vendorId = '')
    {
        $totalPayoutsPending = 0;

        if ($vendorId != '') {
            $ordersPendingPayout = $this->getPendingOrderVendorCollection($vendorId);

            if ($ordersPendingPayout->getSize() > 0) {
                foreach ($ordersPendingPayout as $orderPendingPayout) {
                    $totalPayoutsPending += $orderPendingPayout->getPayoutAmount();
                }
            }
        }

        return $totalPayoutsPending;
    }

    public function getTotalPayoutsPending($vendorId = '')
    {
        $table = $this->getTable('omnyfy_mcm_vendor_order');

        $additionalShippingValue = 0;

        $query = $this->getConnection()->select()->from(
            $table, [
            'total_balance_owing' => 'SUM(payout_amount)',
            'total_balance_owing_tax' => 'SUM(base_tax_amount - total_tax_onfees)',
            'total_balance_owing_net_amount' => 'SUM((base_subtotal - base_discount_amount) + (base_shipping_amount - shipping_discount_amount) - (total_category_fee + total_seller_fee + disbursement_fee))'
        ])->where('payout_status =?', 0);
        if ($vendorId != '') {
            $query = $query->where('vendor_id =?', $vendorId);

            // Check the MCM database if there are orders from the vendor that are pending payout
            $mcmPayoutOrders = $this->getOrdersPendingPayout($vendorId);
            if (count($mcmPayoutOrders) > 0) {
                // If there is find the shipping amount the customer has paid to the vendor
                $additionalShippingValue = $this->getPayoutOrdersShipping($mcmPayoutOrders, $vendorId);
            }
        }
        else {
            // Check the MCM database if there are orders from the vendor that are pending payout
            $mcmPayoutOrders = $this->getOrdersPendingPayout();
            if (count($mcmPayoutOrders) > 0) {
                // If there is find the shipping amount the customer has paid to the vendor
                $additionalShippingValue = $this->getPayoutOrdersShipping($mcmPayoutOrders, $vendorId);
            }
        }
        $result = $this->getConnection()->fetchRow($query);
        if (!empty($result)) {
            // If MCM is set not to manage shipping fees, if ship by type is also disabled, pay out to vendor
            if (!$this->mcmHelper->getShipByTypeConfiguration()) {
                if (!$this->mcmHelper->manageShippingFees()) {
                    // If there is additional shipping value to be added put onto total payout amount
                    if ($additionalShippingValue > 0) {
                        foreach ($result as $totalsKey => $totals) {
                            //                    error_log($totalsKey);
                            if ($totalsKey == 'total_balance_owing' && !empty($totals)) {
                                //                        error_log('additional shipping: ' . $additionalShippingValue);
                                $result[$totalsKey] += $additionalShippingValue;
                            }
                        }
                        if ($vendorId == '') {
                            $result['total_shipping'] = $additionalShippingValue;
                        }
                    }
                }
            }
            return $result;
        }

        return;
    }

    public function getTotalEarning($vendorId = '') {
        $table = $this->getTable('omnyfy_mcm_vendor_order');
        $query = $this->getConnection()->select()->from(
            $table, [
            'total_balance_owing' => 'SUM(payout_amount)',
            'total_balance_owing_tax' => 'SUM(base_tax_amount - total_tax_onfees)',
            'total_balance_owing_net_amount' => 'SUM((base_subtotal - base_discount_amount) + (base_shipping_amount - shipping_discount_amount) - (total_category_fee + total_seller_fee + disbursement_fee))',
            'category_commission_paid' => 'SUM(total_category_fee + total_category_fee_tax)',
            'disbursement_fee_paid' => 'SUM(disbursement_fee + disbursement_fee_tax)',
            'seller_fee_paid' => 'SUM(total_seller_fee + total_seller_fee_tax)'
        ])->where('payout_status !=?', 2);
        if ($vendorId != '') {
            $query = $query->where('vendor_id =?', $vendorId);
        }

        $result = $this->getConnection()->fetchRow($query);
        $result['total_vendor_rebate'] = 0;

        if ($vendorId != '') {
            $vendorRebatesTotals = $this->getVendorTotalEarningCollection($vendorId);

            $totalRebates = 0;
            // If there are records for the vendor
            if (($vendorRebatesTotals) && $vendorRebatesTotals->getSize() > 0) {
                // For all the orders that are pending, loop through the orders to get total payout amount
                foreach($vendorRebatesTotals as $vendorRebatesTotal) {
                    // Calculate the total rebates per order be using the existing rebateOrder() function
                    $vendorRebatesTotal = $this->rebateOrder($vendorId, $vendorRebatesTotal->getOrderId());
                    if (!empty($vendorRebatesTotal)) {
                        $totalRebates += $vendorRebatesTotal;
                    }
                }
            }

            $result['total_vendor_rebate'] = $totalRebates;
        }
        // Get the total rebates
        return $result;

    }

    public function getVendorTotalEarningCollection($vendorId = '')
    {
        $vendorOrderCollection = $this->vendorOrderCollectionFactory->create();
        $vendorOrderCollection = $vendorOrderCollection->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('payout_status', ['neq' => 2]);

        return $vendorOrderCollection;
    }

    public function getTotalMarketplaceEarning() {
        $table = $this->getTable('sales_order');

        $additionalShippingValue = 0;

        $query = $this->getConnection()->select()
            ->from(
                ['so' => $table], []
            )->columns([
                'total_balance_owing' => 'SUM(voo.total_balance_owing + so.mcm_base_transaction_fee_incl_tax)',
                'total_balance_owing_tax' => 'SUM(voo.total_balance_owing_tax + so.mcm_base_transaction_fee_tax)',
                'total_balance_owing_net_amount' => 'SUM(voo.total_balance_owing_net_amount + so.mcm_base_transaction_fee)',
                'category_commission_paid' => 'SUM(voo.category_commission_paid)',
                'disbursement_fee_paid' => 'SUM(voo.disbursement_fee_paid)',
                'seller_fee_paid' => 'SUM(voo.seller_fee_paid)',
                'transaction_fee_paid' => 'SUM(so.mcm_base_transaction_fee_incl_tax)'
            ])->join(['voo' => (
            $this->getConnection()->select()
                ->from(
                    ['vo' => $this->getTable('omnyfy_mcm_vendor_order')], ['order_id']
                )
                ->columns([
                    'total_balance_owing' => 'SUM(vo.total_category_fee + vo.total_category_fee_tax + vo.total_seller_fee + vo.total_seller_fee_tax + vo.disbursement_fee + vo.disbursement_fee_tax )',
                    'total_balance_owing_tax' => 'SUM(vo.total_tax_onfees)',
                    'total_balance_owing_net_amount' => 'SUM(vo.total_category_fee + vo.total_seller_fee + vo.disbursement_fee )',
                    'category_commission_paid' => 'SUM(vo.total_category_fee + vo.total_category_fee_tax)',
                    'disbursement_fee_paid' => 'SUM(vo.disbursement_fee + vo.disbursement_fee_tax)',
                    'seller_fee_paid' => 'SUM(vo.total_seller_fee + vo.total_seller_fee_tax)',
                ])
                ->where('vo.payout_status !=?', 2)
                ->group('order_id')
            )], 'so.entity_id = voo.order_id', ['order_id']
            );

        $result = $this->getConnection()->fetchRow($query);
        if (!empty($result)) {
            // If MCM is set not to manage shipping fees, if ship by type is also disabled, pay out to vendor
            if (!$this->mcmHelper->getShipByTypeConfiguration()) {
                if (!$this->mcmHelper->manageShippingFees()) {
                    $mcmPayoutOrders = $this->getOrdersPaidOut();

                    if (count($mcmPayoutOrders) > 0) {
                        // If there is find the shipping amount the customer has paid to the vendor
                        $additionalShippingValue = $this->getPayoutOrdersCompleted($mcmPayoutOrders);
                    }

                    if ($additionalShippingValue > 0) {
                        foreach ($result as $totalsKey => $totals) {
                            //                    error_log($totalsKey);
                            if ($totalsKey == 'total_balance_owing' && !empty($totals)) {
                                //                        error_log('additional shipping: ' . $additionalShippingValue);
                                $result[$totalsKey] += $additionalShippingValue;
                            }
                        }
                        $result['total_shipping'] = $additionalShippingValue;
                    }
                }
            }
            return $result;
        }
        return;
    }

    public function getPendingPayoutLastUpdated($vendorId = '') {
        $table = $this->getTable('omnyfy_mcm_vendor_order');
        $query = $this->getConnection()->select()->from(
            $table, ['DATE_FORMAT(max(updated_at), "%h:%i %p")'])
            ->where('payout_status =?', 0);
        if ($vendorId != '') {
            $query = $query->where('vendor_id =?', $vendorId);
        }
        $result = $this->getConnection()->fetchOne($query);
        if (!empty($result)) {
            return $result;
        }
        return;
    }

    public function getEarningLastUpdated($vendorId = '') {
        $table = $this->getTable('omnyfy_mcm_vendor_order');
        $query = $this->getConnection()->select()->from(
            $table, ['DATE_FORMAT(max(updated_at), "%h:%i %p")'])
            ->where('payout_status !=?', 2);
        if ($vendorId != '') {
            $query = $query->where('vendor_id =?', $vendorId);
        }
        $result = $this->getConnection()->fetchOne($query);
        if (!empty($result)) {
            return $result;
        }
        return;
    }

    public function getTotalReadyToPay($vendorId = '') {
        $additionalShippingValue = 0;

        $table = $this->getTable('omnyfy_mcm_vendor_order');
        $query = $this->getConnection()->select()->from(
            ['vorder' => $table], [
            'total_payout_amount' => '(SUM(payout_amount))',
            'total_payout_shipping' => 'SUM(payout_shipping)',
            //'SUM((base_grand_total + (shipping_amount + shipping_tax - shipping_discount_amount)) - (total_category_fee + total_category_fee_tax + total_seller_fee + total_seller_fee_tax + disbursement_fee + disbursement_fee_tax))',

            'total_payout_amount_tax' => 'SUM(base_tax_amount - total_tax_onfees)',
            'total_payout_net_amount' => 'SUM((base_subtotal - base_discount_amount) + (base_shipping_amount - shipping_discount_amount) - (total_category_fee + total_seller_fee + disbursement_fee))',
            //'SUM((subtotal - discount_amount) + (shipping_amount - shipping_discount_amount) - (total_category_fee + total_seller_fee + disbursement_fee))',
            'total_fees_paid_incl_tax' => 'SUM(total_category_fee + total_seller_fee + disbursement_fee + total_tax_onfees)',
            'shipping_total_for_order' => 'base_tax_amount'
        ])->where('payout_status =?', 0)
            ->where('payout_action =?', 1);

        if ($vendorId != '') {
            $query = $this->getConnection()->select()->from(
                ['vorder' => $table], [
                'total_payout_amount' => '(SUM(payout_amount))',
                //'SUM((base_grand_total + (shipping_amount + shipping_tax - shipping_discount_amount)) - (total_category_fee + total_category_fee_tax + total_seller_fee + total_seller_fee_tax + disbursement_fee + disbursement_fee_tax))',

                'total_payout_amount_tax' => 'SUM(base_tax_amount - total_tax_onfees)',
                'total_payout_net_amount' => 'SUM((base_subtotal - base_discount_amount) + (base_shipping_amount - shipping_discount_amount) - (total_category_fee + total_seller_fee + disbursement_fee))',
                //'SUM((subtotal - discount_amount) + (shipping_amount - shipping_discount_amount) - (total_category_fee + total_seller_fee + disbursement_fee))',
                'total_fees_paid_incl_tax' => 'SUM(total_category_fee + total_seller_fee + disbursement_fee + total_tax_onfees)'
            ])->where('vorder.payout_status =?', 0)
            ->where('vorder.payout_action =?', 1)
            ->where('vorder.vendor_id = ' .$vendorId);


            // Check the MCM database if there are orders from the vendor that are pending payout
            $mcmPayoutOrders = $this->getOrdersPendingPayout($vendorId);
            if (count($mcmPayoutOrders) > 0) {
                // If there is find the shipping amount the customer has paid to the vendor
                $additionalShippingValue = $this->getPayoutOrdersShipping($mcmPayoutOrders, $vendorId);
            }
        } else {
            // Check the MCM database if there are orders from the vendor that are pending payout
            $mcmPayoutOrders = $this->getOrdersPendingPayout();
            if (count($mcmPayoutOrders) > 0) {
                // If there is find the shipping amount the customer has paid to the vendor
                $additionalShippingValue = $this->getPayoutOrdersShipping($mcmPayoutOrders, $vendorId);
            }
        }

        $result = $this->getConnection()->fetchRow($query);
        if (!empty($result)) {
            // If MCM is set not to manage shipping fees, if ship by type is also disabled, pay out to vendor
            if (!$this->mcmHelper->getShipByTypeConfiguration()) {
                if (!$this->mcmHelper->manageShippingFees()) {
                    $result['total_shipping'] = 0;
                    // If there is additional shipping value to be added put onto total payout amount
                    if ($additionalShippingValue > 0) {
                        foreach ($result as $totalsKey => $totals) {
                            if ($totalsKey == 'total_payout_amount' && !empty($totals)) {
                                $result[$totalsKey] += $additionalShippingValue;
                            }
                        }

                        if ($vendorId == '') {
                            $result['total_shipping'] = $additionalShippingValue;
                        }
                    }
                }
            }

            return $result;
        }
        return;
    }

    public function getOrdersPendingTotal($vendorId = '')
    {
        if ($vendorId != '') {
            $mcmPayoutOrdersQuery = 'SELECT * FROM `omnyfy_mcm_vendor_order` where `payout_status` = 0 AND `payout_action` <> 1 AND `vendor_id` = ' . $vendorId;
        }
        else {
            $mcmPayoutOrdersQuery = 'SELECT * FROM `omnyfy_mcm_vendor_order` where `payout_status` = 0 AND `payout_action` <> 2';
        }

        $mcmPayoutOrders = $this->getConnection()->fetchAll($mcmPayoutOrdersQuery);

        return $mcmPayoutOrders;
    }

    public function getOrdersPendingPayout($vendorId = '')
    {
        if ($vendorId != '') {
            $mcmPayoutOrdersQuery = 'SELECT * FROM `omnyfy_mcm_vendor_order` where `payout_status` = 0 AND `payout_action` = 1 AND `vendor_id` = ' . $vendorId;
        }
        else {
            $mcmPayoutOrdersQuery = 'SELECT * FROM `omnyfy_mcm_vendor_order` where `payout_status` = 0 AND `payout_action` = 1';
        }

        $mcmPayoutOrders = $this->getConnection()->fetchAll($mcmPayoutOrdersQuery);

        return $mcmPayoutOrders;
    }

    public function getOrdersCompletedPayout($vendorId = '')
    {
        if ($vendorId != '') {
            $mcmPayoutOrdersQuery = 'SELECT * FROM `omnyfy_mcm_vendor_order` where `payout_status` = 1 AND `payout_action` = 1 AND `vendor_id` = ' . $vendorId;
        }
        else {
            $mcmPayoutOrdersQuery = 'SELECT * FROM `omnyfy_mcm_vendor_order` where `payout_status` = 1 AND `payout_action` = 1';
        }

        $mcmPayoutOrders = $this->getConnection()->fetchAll($mcmPayoutOrdersQuery);

        return $mcmPayoutOrders;
    }

    public function getOrdersPaidOut()
    {
        $mcmPayoutOrdersQuery = 'SELECT * FROM `omnyfy_mcm_vendor_order` where `payout_status` = 1 AND `payout_action` = 1';

        $mcmPayoutOrders = $this->getConnection()->fetchAll($mcmPayoutOrdersQuery);

        return $mcmPayoutOrders;
    }

    public function getPayoutOrdersShipping($mcmPayoutOrders, $vendorId = '')
    {
        $shippingAmount = 0;

        if (count($mcmPayoutOrders) < 1) {
            return $shippingAmount;
        }

        foreach ($mcmPayoutOrders as $mcmPayoutOrder) {
            $order = $this->orderRepository->get($mcmPayoutOrder['order_id']);
            // get quote id
            if ($order) {
                if ($vendorId != '') {
                    $vendorShippingQuery = 'SELECT SUM(`amount`) from `omnyfy_vendor_quote_shipping` where `quote_id` = ' . $order->getQuoteId() . ' AND `vendor_id` = ' . $vendorId;
                } else {
                    $vendorShippingQuery = 'SELECT SUM(`amount`) from `omnyfy_vendor_quote_shipping` where `quote_id` = ' . $order->getQuoteId() . ' AND `vendor_id` = ' . $mcmPayoutOrder['vendor_id'];
                }
                $vendorShippingAmount = $this->getConnection()->fetchOne($vendorShippingQuery);
                $shippingAmount += $vendorShippingAmount;
            }
        }

        return $shippingAmount;
    }

    public function getPayoutOrdersCompleted($mcmPayoutOrders)
    {
        $shippingAmount = 0;
        if (count($mcmPayoutOrders) < 1) {
            return $shippingAmount;
        }

        foreach ($mcmPayoutOrders as $mcmPayoutOrder) {
            $order = $this->orderRepository->get($mcmPayoutOrder['order_id']);
            // get quote id
            if ($order) {
                $pickupLocation = $this->shippingHelper->getShippingConfiguration('overall_pickup_location');
                // error_log('pickup location: ' .$pickupLocation);
                // error_log('pickup location by: ' . $this->shippingHelper->getCalculateShippingBy());
                if ($this->shippingHelper->getCalculateShippingBy() == 'overall_cart' && !empty($pickupLocation)) {
                    $pickupLocation = $this->shippingHelper->getShippingConfiguration('overall_pickup_location');
                    if (!empty($pickupLocation)) {
                        $vendorShippingQuery = 'SELECT SUM(`amount`) from `omnyfy_vendor_quote_shipping` where `quote_id` = ' . $order->getQuoteId() . ' AND `location_id` = ' . $pickupLocation;
                    }
                } else {
                    // error_log('quote id: ' . $$order->getQuoteId());
                    $vendorShippingQuery = 'SELECT SUM(`amount`) from `omnyfy_vendor_quote_shipping` where `quote_id` = ' . $order->getQuoteId() . ' AND `vendor_id` = ' . $mcmPayoutOrder['vendor_id'];
                }
                $vendorShippingAmount = $this->getConnection()->fetchOne($vendorShippingQuery);
                $shippingAmount += $vendorShippingAmount;
            }
        }

        return $shippingAmount;
    }

    public function getShippingCollectedTotal($vendorId = '')
    {
        $omnyfyVendorQuoteShippingTable = $this->getTable('omnyfy_vendor_quote_shipping');
        $shippingTotalQuery = 'SELECT SUM(amount) from '.$omnyfyVendorQuoteShippingTable;

        if ($vendorId != '') {
            $shippingTotalQuery .= 'where `vendor_id` = ' . $vendorId;
        }

        $data = $this->getConnection()->fetchOne($shippingTotalQuery);

        return $data;
    }

    public function getReadyToPayLastUpdated($vendorId = '') {
        $table = $this->getTable('omnyfy_mcm_vendor_order');
        $query = $this->getConnection()->select()->from(
            $table, ['DATE_FORMAT(max(updated_at), "%h:%i %p")'])
            ->where('payout_status =?', 0)
            ->where('payout_action =?', 1);
        if ($vendorId != '') {
            $query = $query->where('vendor_id =?', $vendorId);
        }
        $result = $this->getConnection()->fetchOne($query);
        if (!empty($result)) {
            return $result;
        }
        return;
    }

    public function getTotalPendingPayoutOrder($vendorId = '') {
        $table = $this->getTable('omnyfy_mcm_vendor_order');
        $query = $this->getConnection()->select()->from(
            $table, [
            'total_payout_amount' => 'SUM(payout_amount)',
            //'SUM((grand_total + (shipping_amount + shipping_tax - shipping_discount_amount)) - (total_category_fee + total_category_fee_tax + total_seller_fee + total_seller_fee_tax + disbursement_fee + disbursement_fee_tax))',
            'total_fees_paid_incl_tax' => 'SUM(total_category_fee + total_seller_fee + disbursement_fee + total_tax_onfees)'
        ])
            ->where('payout_status =?', 0)
            ->where('payout_action =?', 0);
        if ($vendorId != '') {
            $query = $query->where('vendor_id =?', $vendorId);
        }

        $result = $this->getConnection()->fetchRow($query);
        if (!empty($result)) {
            return $result;
        }
        return;
    }

    public function updateData($data, $conditions, $tableName = 'omnyfy_request_quote_item') {
        $table = $this->getTable($tableName);
        if (!empty($data)) {
            $this->getConnection()->update($table, $data, ['id = ?' => $id]);
        }
    }

    public function getTotalPayoutsPendingCurrentMonth($vendorId = '') {
        $table = $this->getTable('omnyfy_mcm_vendor_order');
        $currentMonth = date('n');
        $currentYear = date('Y');
        $query = $this->getConnection()->select()->from(
            $table, [
            'total_balance_owing' => 'SUM((base_grand_total + (base_shipping_amount - shipping_discount_amount)) - (total_category_fee + total_category_fee_tax + total_seller_fee + total_seller_fee_tax + disbursement_fee + disbursement_fee_tax))',
            //'SUM((grand_total + (shipping_amount + shipping_tax - shipping_discount_amount)) - (total_category_fee + total_category_fee_tax + total_seller_fee + total_seller_fee_tax + disbursement_fee + disbursement_fee_tax))',
            'total_balance_owing_tax' => 'SUM(base_tax_amount - total_tax_onfees)',
            'total_balance_owing_net_amount' => 'SUM((base_subtotal - base_discount_amount) + (base_shipping_amount - shipping_discount_amount) - (total_category_fee + total_seller_fee + disbursement_fee))'
            //'SUM((subtotal - discount_amount) + (shipping_amount - shipping_discount_amount) - (total_category_fee + total_seller_fee + disbursement_fee))'
        ])->where('payout_status =?', 0)
            ->where('MONTH(created_at) =?', $currentMonth)
            ->where('YEAR(created_at) =?', $currentYear);
        if ($vendorId != '') {
            $query = $query->where('vendor_id =?', $vendorId);
        }
        $result = $this->getConnection()->fetchRow($query);
        if (!empty($result)) {
            return $result;
        }
        return;
    }

    public function getTotalEarningCurrentMonth($vendorId = '') {
        $additionalShippingValue = 0;

        $table = $this->getTable('omnyfy_mcm_vendor_order');
        $currentMonth = date('n');
        $currentYear = date('Y');
        $query = $this->getConnection()->select()->from(
            $table, [
            'total_balance_owing' => 'SUM((base_grand_total + (base_shipping_amount - shipping_discount_amount)) - (total_category_fee + total_category_fee_tax + total_seller_fee + total_seller_fee_tax + disbursement_fee + disbursement_fee_tax))',
            //'SUM((grand_total + (shipping_amount + shipping_tax - shipping_discount_amount)) - (total_category_fee + total_category_fee_tax + total_seller_fee + total_seller_fee_tax + disbursement_fee + disbursement_fee_tax))',
            'total_balance_owing_tax' => 'SUM(base_tax_amount - total_tax_onfees)',
            'total_balance_owing_net_amount' => 'SUM((base_subtotal - base_discount_amount) + (base_shipping_amount - shipping_discount_amount) - (total_category_fee + total_seller_fee + disbursement_fee))',
            //'SUM((subtotal - discount_amount) + (shipping_amount - shipping_discount_amount) - (total_category_fee + total_seller_fee + disbursement_fee))',
            'category_commission_paid' => 'SUM(total_category_fee + total_category_fee_tax)',
            'disbursement_fee_paid' => 'SUM(disbursement_fee + disbursement_fee_tax)',
            'seller_fee_paid' => 'SUM(total_seller_fee + total_seller_fee_tax)'
        ])->where('MONTH(created_at) =?', $currentMonth)
            ->where('YEAR(created_at) =?', $currentYear)
            ->where('payout_status !=?', 2);
        if ($vendorId != '') {
            $query = $query->where('vendor_id =?', $vendorId);
        } else {
            $mcmPayoutOrders = $this->getOrdersPendingTotal();
            if (count($mcmPayoutOrders) > 0) {
                // If there is find the shipping amount the customer has paid to the vendor
                $additionalShippingValue = $this->getPayoutOrdersShipping($mcmPayoutOrders, $vendorId);
            }
        }

        $result = $this->getConnection()->fetchRow($query);
        if (!empty($result)) {
            // If MCM is set not to manage shipping fees, if ship by type is also disabled, pay out to vendor
            if (!$this->mcmHelper->getShipByTypeConfiguration()) {
                if (!$this->mcmHelper->manageShippingFees()) {
                    if ($additionalShippingValue > 0) {
                        foreach ($result as $totalsKey => $totals) {
                            if ($totalsKey == 'total_balance_owing' && !empty($totals)) {
                                $result[$totalsKey] += $additionalShippingValue;
                            }
                        }
                        if ($vendorId == '') {
                            $result['total_shipping'] = $additionalShippingValue;
                        }
                    }
                }
            }

            return $result;
        }
        return;
    }

    public function getTotalMarketplaceEarningCurrentMonth() {
        $table = $this->getTable('sales_order');
        $currentMonth = date('n');
        $currentYear = date('Y');
        $query = $this->getConnection()->select()
            ->from(
                ['so' => $table], ['entity_id']
            )->columns([
                'total_balance_owing' => 'SUM(voo.total_balance_owing + so.mcm_base_transaction_fee_incl_tax)',
                'total_balance_owing_tax' => 'SUM(voo.total_balance_owing_tax + so.mcm_base_transaction_fee_tax)',
                'total_balance_owing_net_amount' => 'SUM(voo.total_balance_owing_net_amount + so.mcm_base_transaction_fee)',
                'category_commission_paid' => 'SUM(voo.category_commission_paid)',
                'disbursement_fee_paid' => 'SUM(voo.disbursement_fee_paid)',
                'seller_fee_paid' => 'SUM(voo.seller_fee_paid)',
                'transaction_fee_paid' => 'SUM(so.mcm_base_transaction_fee_incl_tax)'
            ])->join(['voo' => (
            $this->getConnection()->select()
                ->from(
                    ['vo' => $this->getTable('omnyfy_mcm_vendor_order')], ['order_id']
                )
                ->columns([
                    'total_balance_owing' => 'SUM(vo.total_category_fee + vo.total_category_fee_tax + vo.total_seller_fee + vo.total_seller_fee_tax + vo.disbursement_fee + vo.disbursement_fee_tax )',
                    'total_balance_owing_tax' => 'SUM(vo.total_tax_onfees)',
                    'total_balance_owing_net_amount' => 'SUM(vo.total_category_fee + vo.total_seller_fee + vo.disbursement_fee )',
                    'category_commission_paid' => 'SUM(vo.total_category_fee + vo.total_category_fee_tax)',
                    'disbursement_fee_paid' => 'SUM(vo.disbursement_fee + vo.disbursement_fee_tax)',
                    'seller_fee_paid' => 'SUM(vo.total_seller_fee + vo.total_seller_fee_tax)',
                ])
                ->where('MONTH(vo.created_at) =?', $currentMonth)
                ->where('YEAR(vo.created_at) =?', $currentYear)
                ->where('vo.payout_status !=?', 2)
                ->group('order_id')
            )], 'so.entity_id = voo.order_id', ['order_id']
            );
        //echo $query;

        $result = $this->getConnection()->fetchRow($query);
        if (!empty($result)) {
            return $result;
        }
        return;
    }

    public function updateWalletInfo($vendorId, $eWalletId, $accountRef, $thirdPartyAccountId) {
        $conn = $this->getConnection();
        $table = $this->getMainTable();

        $conn->update(
            $table,
            [
                'ewallet_id' => $eWalletId,
                'account_ref' => $accountRef,
                'third_party_account_id' => $thirdPartyAccountId
            ],
            ['vendor_id=?' => $vendorId]
        );
    }

    public function updateAccountRef($vendorId, $accountRef) {
        if (empty($vendorId) || empty($accountRef)) {
            return;
        }

        $conn = $this->getConnection();
        $table = $this->getMainTable();
        if (empty($conn) || empty($table)) {
            return;
        }

        $conn->update($table, ['account_ref' => $accountRef], ['vendor_id=?' => $vendorId]);
    }

    /**
     * @param $vendorId
     * @return float|int
     */
    public function getTotalReadyToPayByWholesaleVendor($vendorId) {
        $additionalShippingValue = 0;

        $table = $this->getTable('omnyfy_mcm_vendor_order');

        // select orders that are ready to payout per vendor
        $query = $this->getConnection()->select()->from(
            ['vorder' => $table], ['*']
        )->where('vorder.payout_status =?', 0)
        ->where('vorder.payout_action =?', 1)
        ->where('vorder.vendor_id = ' .$vendorId);

        $amount = 0;
        // Check the MCM database if there are orders from the vendor that are pending payout
        $mcmPayoutOrders = $this->getOrdersPendingPayout($vendorId);

        $result = $this->getConnection()->fetchAll($query);
        if (!empty($result)) {
            // If MCM is set not to manage shipping fees, if ship by type is also disabled, pay out to vendor
            foreach ($result as $orderVendor) {
                $amount += $this->getTotalOrderByWholesaleVendor($orderVendor['order_id'], $orderVendor['vendor_id']);
            }
             $mcmPayoutOrders = $this->getOrdersPendingPayout();

            if (count($mcmPayoutOrders) > 0) {
                // If there is additional shipping, find the shipping amount the customer has paid that will go to the vendor
                $additionalShippingValue = $this->getPayoutOrdersShipping($mcmPayoutOrders, $vendorId);
            }

            if (!$this->mcmHelper->getShipByTypeConfiguration()) {
                if (!$this->mcmHelper->manageShippingFees()) {
                    $result['total_shipping'] = 0;
                    // If there is additional shipping value to be added put onto total payout amount
                    if ($additionalShippingValue > 0) {
                        $amount += $additionalShippingValue;
                    }
                }
            }
        }
        return $amount;
    }

    /**
     * @param $orderId
     * @param $vendorId
     * @return float|int
     */
    public function getTotalOrderByWholesaleVendor($orderId, $vendorId){
        $items = $this->vendorOrderResourceFactory->create()->getOrderItems($orderId, $vendorId);
        $total = 0;
        $arrItemVendor = [];
        foreach ($items as $item) {
            $arrItemVendor[] =  $item['order_item_id'];
        }
        $order = $this->orderRepository->get($orderId);
        $itemsOrder = $order->getAllVisibleItems();
        foreach ($itemsOrder as $itemOrder) {
          if (in_array($itemOrder->getId(), $arrItemVendor)) {
                if ($itemOrder->getProductType() == "configurable") {
                    $childItems = $itemOrder->getChildrenItems();
                    foreach($childItems as $item){
                        $cost = $item->getBaseCost();
                        $qty = $item->getQtyOrdered() - $item->getQtyRefunded();
                        $total += $cost * $qty;
                   }
                }else{
                    $cost = $itemOrder->getBaseCost();
                    $qty = $itemOrder->getQtyOrdered() - $itemOrder->getQtyRefunded();
                    $total += $cost * $qty;
                };
            }
        }

        // Total should only show cost * qty ordered, do not need rebate
        $rebateOrder = $this->rebateOrder($vendorId, $orderId);
        if (!empty($rebateOrder)) {
            $total -= $rebateOrder;
        }

        $vendorMcmOrder = $this->getVendorOrder($orderId, $vendorId);

        if (!$this->mcmHelper->manageShippingFees()) {
            if ($vendorMcmOrder && isset($vendorMcmOrder['shipping_incl_tax'])) {
                $total += $vendorMcmOrder['shipping_incl_tax'];
            }
        }

        return $total;
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


    /**
     * @param $vendorId
     * @return float|int
     */
    public function getOrdersRebateTotal($vendorId, $type = NULL)
    {
        // Ensure a type if passed through before processing
        if ($type == NULL) {
            return 0;
        }

        // Get all vendor orders that are pending
        if ($type == 'pending_orders') {
            $vendorRebatesTotals = $this->getPendingOrderVendorCollection($vendorId);
        } elseif ($type == 'orders_included_in_payout') {
            $vendorRebatesTotals = $this->getReadyToPayoutVendorOrderCollection($vendorId);
        }

        $total = 0;
        $totalRebates = 0;

        // If there are records for the vendor
        if (($vendorRebatesTotals) && $vendorRebatesTotals->getSize() > 0) {

            // For all the orders that are pending, loop through the orders to get total payout amount
            foreach($vendorRebatesTotals as $vendorRebatesTotal) {
                $total += $vendorRebatesTotal->getPayoutAmount();

                // Calculate the total rebates per order be using the existing rebateOrder() function
                $vendorTotalRebates = $this->rebateOrder($vendorId, $vendorRebatesTotal->getOrderId());
                if (!empty($vendorTotalRebates)) {
                    $totalRebates += $vendorTotalRebates;
                }
            }
        }

        // The total pending order total value
        $vendorTotalPending = $total - $totalRebates;

        return $vendorTotalPending;
    }

    /**
     * @param $orderId
     * @param $vendorId
     * @return float|int
     */
    public function getPayoutTotalWholesaleVendor($orderId, $vendorId, $mcmOrderId = null, $isCostCalculationOnly = false){
        $items = $this->vendorOrderResourceFactory->create()->getOrderItems($orderId, $vendorId);
        $total = 0;
        $arrItemVendor = [];
        foreach ($items as $item) {
            $arrItemVendor[] =  $item['order_item_id'];
        }
        $order = $this->orderRepository->get($orderId);
        $itemsOrder = $order->getAllVisibleItems();
        foreach ($itemsOrder as $itemOrder) {
            if (in_array($itemOrder->getId(), $arrItemVendor)) {
                if ($itemOrder->getProductType() == "configurable") {
                    $childItems = $itemOrder->getChildrenItems();
                    foreach($childItems as $item){
                        $cost = $item->getBaseCost();
                        $qty = $item->getQtyOrdered() - $item->getQtyRefunded();
                        $total += $cost * $qty;
                    }
                }else{
                    $cost = $itemOrder->getBaseCost();
                    $qty = $itemOrder->getQtyOrdered() - $itemOrder->getQtyRefunded();
                    $total += $cost * $qty;
                };
            }
        }

        // @TODO - Add shipping to total depending on mcm configuration for MO to retain shipping
        $vendorMcmOrder = $this->getVendorOrder($orderId, $vendorId);

        if (!$isCostCalculationOnly) {
            if (!$this->mcmHelper->manageShippingFees()) {
                if ($vendorMcmOrder && isset($vendorMcmOrder['shipping_incl_tax'])) {
                    $total += $vendorMcmOrder['shipping_incl_tax'];
                }
            }
        }

        return $total;
    }

    public function getTotalOrderByCommissionVendor($orderId, $vendorId)
    {
        $dataVendorOrder = $this->getVendorOrder($orderId, $vendorId);
        $total = $dataVendorOrder['base_subtotal_incl_tax'] - ($dataVendorOrder['total_category_fee_incl_tax'] + $dataVendorOrder['total_seller_fee_incl_tax'] + $dataVendorOrder['total_disbursement_fee_incl_tax']);

        $rebateOrder = $this->rebateOrder($vendorId, $orderId);
        if (!empty($rebateOrder)) {
            $total -= $rebateOrder;
        }

        return $total;
    }

    public function getPayoutAmount($vendorId, $orderId)
    {
        $dataVendorOrder = $this->getVendorOrder($orderId, $vendorId);

        if ($this->getVendorPayoutBasisType($vendorId) == PayoutBasisType::WHOLESALE_VENDOR_VALUE) {
            $payoutAmount = $this->getTotalOrderByWholesaleVendor($orderId, $vendorId);
        } elseif ($this->getVendorPayoutBasisType($vendorId) == PayoutBasisType::COMMISSION_VENDOR_VALUE) {
            $payoutAmount = $this->getPayoutAmountCommission($vendorId, $orderId, $dataVendorOrder['payout_amount']);
        } else {
            $payoutAmount = $dataVendorOrder['payout_amount'];
        }

        return $payoutAmount;
    }

    public function getPayoutAmountCommission($vendorId, $orderId, $payoutAmount)
    {
        $rebateOrder = $this->rebateOrder($vendorId, $orderId);
        if ($payoutAmount == 0) {
            return $payoutAmount;
        }
        if (!empty($rebateOrder)) {
            $payoutAmount -= $rebateOrder;
        }

        return $payoutAmount;
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getVendorPayoutBasisType($vendorId){
        $vendor = $this->vendorRepository->getById($vendorId);
        return $vendor->getPayoutBasisType();
    }

    public function rebateOrder($vendorId, $orderId)
    {
        return $this->transactionRebateRepository->getPerOrderSettlementTransactions($vendorId, $orderId);
    }
}
