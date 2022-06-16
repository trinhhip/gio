<?php
namespace Omnyfy\Order\Helper;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /*
     * @var \Omnyfy\Mcm\Helper\Data
     */
    protected $mcmHelper;

    /*
     * @var \Omnyfy\Mcm\Model\ResourceModel\FeesManagement
     */
    protected $feesManagementResource;

    /**
     * @var \Omnyfy\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Omnyfy\Mcm\Helper\Data $mcmHelper
     * @param \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $feesManagementResource
     * @param \Omnyfy\Vendor\Helper\Data $vendorHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Omnyfy\Mcm\Helper\Data $mcmHelper,
        \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $feesManagementResource,
        \Omnyfy\Vendor\Helper\Data $vendorHelper
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->connection = $resource->getConnection();
        $this->quoteRepository = $quoteRepository;
        $this->mcmHelper = $mcmHelper;
        $this->feesManagementResource = $feesManagementResource;
        $this->vendorHelper = $vendorHelper;
    }

    public function getProductQty($vendorId, $productSku, $sourceStockId)
    {
        $select = $this->connection->select()
            ->from(['source_stock' => $this->connection->getTableName('omnyfy_vendor_source_stock')], [])
            ->join(
                ['source_item' => $this->connection->getTableName('inventory_source_item')],
                'source_stock.source_code = source_item.source_code',
                ['quantity']
            )
            ->where('vendor_id = ?', $vendorId)
            ->where('sku like ?', $productSku)
            ->where('status = 1')
            ->where('id = ?', $sourceStockId);
        return $this->connection->fetchOne($select);
    }

    public function getSourceStockIdsByVendorId($vendorId)
    {
        $select = $this->connection->select()
            ->from($this->connection->getTableName('omnyfy_vendor_source_stock'), 'id')
            ->where('vendor_id = ?', $vendorId);
        $sourceStockIds = $this->connection->fetchCol($select);

        return $sourceStockIds;
    }

    public function getVendorItemsTotals($order, $vendorId)
    {
        $orderId = $order->getId();
        $vendorItemsTotals = $this->feesManagementResource->getVendorItemsTotals($vendorId, $orderId);
        $grandTotal = $vendorItemsTotals['row_total'] + $vendorItemsTotals['tax_amount'] - $vendorItemsTotals['discount_amount'];
        $baseGrandTotal = ($vendorItemsTotals['base_row_total'] + $vendorItemsTotals['base_tax_amount'] - ($vendorItemsTotals['base_discount_amount']));

        $vendorOrder = [
            'order_id' => $orderId,
            'vendor_id' => $vendorId,
            'subtotal' => $vendorItemsTotals['row_total'],
            'base_subtotal' => $vendorItemsTotals['base_row_total'],
            'subtotal_incl_tax' => $vendorItemsTotals['row_total_incl_tax'],
            'base_subtotal_incl_tax' => $vendorItemsTotals['base_row_total_incl_tax'],
            'tax_amount' => $vendorItemsTotals['tax_amount'],
            'base_tax_amount' => $vendorItemsTotals['base_tax_amount'],
            'discount_amount' => $vendorItemsTotals['discount_amount'],
            'base_discount_amount' => $vendorItemsTotals['base_discount_amount'],
            'grand_total' => $grandTotal,
            'base_grand_total' => $baseGrandTotal
        ];
        return $vendorOrder;
    }

    public function getShippingFeesPerVendor($order, $vendorId)
    {
        $shippingFeeData = [];

        $orderId = $order->getId();
        $quoteId = $order->getQuoteId();
        try {
            $quote = $this->quoteRepository->get($quoteId);
        } catch (\Exception $e) {
            $quote = null;
        }
        if (empty($quote)) {
            return null;
        }

        // Set shipping fee data with $0 - overallcart will not match a vendor
        $shippingFeeData = [
            'order_id' => $orderId,
            'vendor_id' => $vendorId,
            'shipping_amount' => 0,
            'base_shipping_amount' => 0,
            'shipping_incl_tax' => 0,
            'base_shipping_incl_tax' => 0,
            'shipping_tax' => 0,
            'base_shipping_tax' => 0,
            'shipping_discount_amount' => 0,
            'shipping_description' => ''
        ];

        $shippingAddress = $quote->getShippingAddress();
        $rates = $shippingAddress->getAllShippingRates();

        $shippingMethod = $this->vendorHelper->shippingMethodStringToArray($order->getShippingMethod());

        $percentages = $this->mcmHelper->getShippingTaxPercent($orderId);

        foreach ($rates as $rate) {
            if ($vendorId == $rate->getVendorId()) {
                $shipping_amount = $rate->getPrice();
                $base_shipping_amount = $rate->getPrice();

                $sourceStockId = $rate->getSourceStockId();

                if (!array_key_exists($sourceStockId, $shippingMethod)) {
                    continue;
                }
                if ($shippingMethod[$sourceStockId] != $rate->getCode()) {
                    continue;
                }

                $shipping_tax = 0;
                foreach ($percentages as $taxCode => $percentage) {
                    // If shipping is inclusive of tax
                    if ($this->scopeConfig->getValue('tax/calculation/shipping_includes_tax')) {
                        $shipping_tax += $rate->getPrice() * $percentage / (100 + $percentage);
                    } else {
                        // else if shipping is exclusive of tax
                        $shipping_tax += $rate->getPrice() * ($percentage / 100);
                    }
                }

                $base_shipping_tax = $shipping_tax;

                if ($this->scopeConfig->getValue('tax/calculation/shipping_includes_tax')) {
                    // If shipping is inclusive of tax
                    $shipping_incl_tax = $rate->getPrice();
                    $base_shipping_incl_tax = $rate->getPrice();
                    $shipping_amount = $rate->getPrice() - $shipping_tax;
                    $base_shipping_amount = $rate->getPrice() - $shipping_tax;
                } else {
                    // else if shipping is exclusive of tax
                    $shipping_incl_tax = $rate->getPrice() + $shipping_tax;
                    $base_shipping_incl_tax = $rate->getPrice() + $shipping_tax;
                }

                $shipping_type = $this->scopeConfig->getValue('carriers/' . $rate->getCarrier() . '/type');
                $vendorNoonOrder = $this->feesManagementResource->getVendorNosOnOrder($orderId);
                if ($shipping_type == 'I') {
                    $totalQtyOrdered = $order->getTotalQtyOrdered();
                    $discountoneachproduct = $order->getBaseShippingDiscountAmount() / $totalQtyOrdered;
                    $qtyforvendor = $this->feesManagementResource->getQtyForVendor($orderId, $vendorId);
                    $shippingVendordiscount = $discountoneachproduct * $qtyforvendor;
                } elseif ($shipping_type == 'O') {
                    $shippingVendordiscount = $order->getBaseShippingDiscountAmount() / $vendorNoonOrder;
                } else {
                    $shippingVendordiscount = 0;
                }

                if ($shippingVendordiscount > $base_shipping_amount) {
                    $shippingVendordiscount = $base_shipping_amount;
                }

                $shippingFeeData = [
                    'order_id' => $orderId,
                    'vendor_id' => $vendorId,
                    'shipping_amount' => number_format((float) $shipping_amount, 2, '.', ''),
                    'base_shipping_amount' => number_format((float) $base_shipping_amount, 2, '.', ''),
                    'shipping_incl_tax' => number_format((float) $shipping_incl_tax, 2, '.', ''),
                    'base_shipping_incl_tax' => number_format((float) $base_shipping_incl_tax, 2, '.', ''),
                    'shipping_tax' => number_format((float) $shipping_tax, 2, '.', ''),
                    'base_shipping_tax' => number_format((float) $base_shipping_tax, 2, '.', ''),
                    'shipping_discount_amount' => number_format((float) $shippingVendordiscount, 2, '.', ''),
                    'shipping_description' => $rate->getCarrierTitle()." - ".$rate->getMethodTitle()
                ];
            }
        }
        return $shippingFeeData;
    }
}
