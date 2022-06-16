<?php
namespace Omnyfy\Easyship\Block\Adminhtml\Order;

class ShipmentCreate extends \Magento\Framework\View\Element\Template
{
    protected $order;
    protected $courierCollectionFactory;
    protected $vendorResource;
    protected $shipCollectionFactory;
    protected $labelCollectionFactory;
    protected $priceHelper;
    protected $shipFactory;
    protected $vSourceStockResource;
    protected $connection;
    protected $quoteItemCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $order,
        \Omnyfy\Easyship\Model\ResourceModel\EasyshipSelectedCourier\CollectionFactory $courierCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource,
        \Omnyfy\Easyship\Model\ResourceModel\EasyshipShipment\CollectionFactory $shipCollectionFactory,
        \Omnyfy\Easyship\Model\ResourceModel\EasyshipShipmentLabel\CollectionFactory $labelCollectionFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Omnyfy\Easyship\Model\EasyshipShipmentFactory $shipFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->order = $order;
        $this->courierCollectionFactory = $courierCollectionFactory;
        $this->vendorResource = $vendorResource;
        $this->shipCollectionFactory = $shipCollectionFactory;
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->priceHelper = $priceHelper;
        $this->shipFactory = $shipFactory;
        $this->vSourceStockResource = $vSourceStockResource;
        $this->connection = $connection;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
    }

    public function getSelectedCourier($orderId, $sourceStockId){
        $order = $this->order->get($orderId);
        $quoteId = $order->getQuoteId();
        $couriers = $this->courierCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('source_stock_id', $sourceStockId);

        if ($couriers->count() > 0 && $couriers->getFirstItem()->getCourierId() != null) {
            return $couriers->getFirstItem();
        }else{
            return null;
        }
    }

    public function getQuoteShipping($quoteId, $sourceCode, $sourceStockId) {
        if (empty($sourceCode) || empty($sourceCode) || empty($sourceStockId) || empty($quoteId)) {
            return null;
        }
        $shipping = $this->vendorResource->getQuoteShipping($quoteId, $sourceStockId);
        if (count($shipping) > 0) {
            foreach ($shipping as $value) {
               if ($this->isProductAssignToSource($sourceCode, $value['quote_id'], $sourceStockId)) {
                   return $value;
               }
            }
        }else{
            return null;
        }
    }

    public function getEasyshipShipmentDetail($orderId, $sourceStockId){
        $shipment = $this->shipCollectionFactory->create()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('source_stock_id', $sourceStockId);
        $shipment->setOrder('created_at', 'DESC');

        if ($shipment->count() > 0 && $shipment->getFirstItem()->getEasyshipShipmentId() != null) {
            return $shipment->getFirstItem();
        }else{
            return null;
        }
    }

    public function getLabelUrl($orderId, $sourceStockId){
        $shipment = $this->getEasyshipShipmentDetail($orderId, $sourceStockId);

        if ($shipment != null && $shipment->getStatus() != 'cancelled') {
            $labels = $this->labelCollectionFactory->create()
                ->addFieldToFilter('easyship_shipment_id', $shipment->getEasyshipShipmentId());

            if ($labels->count() > 0 && $labels->getFirstItem()->getLabelUrl() != null) {
                return $labels->getFirstItem()->getLabelUrl();
            }else{
                return null;
            }
        }else{
            return null;
        }
    }

    public function addCurrencyToAmount($amount){
        $formattedCurrencyValue = $this->priceHelper->currency($amount, true, false);
        return $formattedCurrencyValue;
    }

    public function isEasyshipEnabled(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enable = $this->_scopeConfig->getValue('carriers/easyship/active', $storeScope);
        return $enable;
    }

    public function canCancelShipment($orderId, $sourceStockId, $courierEntityId){
        $shipModel = $this->shipFactory->create()->getEasyshipShipmentIdByParams($orderId, $sourceStockId, $courierEntityId);
        if(($shipModel != null) && ($shipModel->getStatus() != 'cancelled')){
            return true;
        }
        return false;
    }

    public function isProductAssignToSource($sourceCode, $quoteId, $sourceStockId) {
        $quoteItemCollection = $this->quoteItemCollectionFactory->create();
        $quoteItemCollection->addFieldToFilter('quote_id', $quoteId)->addFieldToFilter('source_stock_id', $sourceStockId);

        $skus = [];
        foreach ($quoteItemCollection as $item) {
            $skus[] = $item->getSku();
        }

        $conn = $this->connection->getConnection();
        $table = $conn->getTableName('inventory_source_item');
        $select = $conn->select()->from($table, ['source_code'])
                                ->where('sku IN (?)', $skus);
        $rows = $conn->fetchAll($select);
        $sourceCodes = [];
        foreach ($rows as $row) {
            $sourceCodes[] = $row['source_code'];
        }

        if (in_array($sourceCode, $sourceCodes)) {
            return true;
        } else {
            return false;
        }
    }
    public function getFormatPrice($price){
        return $this->priceHelper->currency($price,true,false);
    }
}
