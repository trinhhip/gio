<?php
namespace Omnyfy\Easyship\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Omnyfy\Easyship\Model\EasyshipQuoteItemCourierFactory;
use Omnyfy\Easyship\Model\EasyshipSelectedCourierFactory;
use Omnyfy\Easyship\Model\EasyshipSalesOrderCourierFactory;
use Omnyfy\Easyship\Model\EasyshipVendorSalesOrderItemCourierFactory;
use Magento\Framework\App\Response\Http as Response;

class Data extends AbstractHelper
{
    /**
     * @var EasyshipQuoteItemCourierFactory
     */
    protected $_quoteItemCourier;

    /**
     * @var EasyshipSelectedCourierFactory
     */
    protected $_selectedCourier;

    /**
     * @var EasyshipSalesOrderCourierFactory
     */
    protected $_salesOrderCourier;

    /**
     * @var EasyshipVendorSalesOrderItemCourierFactory
     */
    protected $_salesOrderItemCourier;

    /**
     * @var Response
     */
    protected $_response;

    public function __construct(
        Context $context,
        EasyshipQuoteItemCourierFactory $quoteItemCourier, 
        EasyshipSelectedCourierFactory $selectedCourier, 
        EasyshipSalesOrderCourierFactory $salesOrderCourier, 
        EasyshipVendorSalesOrderItemCourierFactory $salesOrderItemCourier,
        Response $response
    )
    {
        $this->_quoteItemCourier = $quoteItemCourier;
        $this->_selectedCourier = $selectedCourier;
        $this->_salesOrderCourier = $salesOrderCourier;
        $this->_salesOrderItemCourier = $salesOrderItemCourier;
        $this->_response = $response;
        parent::__construct($context);
    }
    
    public function saveSelectedCourier($data)
    {
        try {
            // save to omnyfy_easyship_selected_courier
            $modSelectedCourier = $this->_selectedCourier->create();
            $modSelectedCourier->setQuoteId($data['quote_id']);
            $modSelectedCourier->setVendorLocationId($data['vendor_location_id']);
            $modSelectedCourier->setCourierId($data['courier_id']);
            $modSelectedCourier->setCourierName($data['courier_name']);
            $modSelectedCourier->setShippingRateOptionId($data['shipping_rate_option_id']);
            $modSelectedCourier->setTotalCharge($data['total_charge']);
            $modSelectedCourier->setCustomerPaid($data['total_charge']);
            $modSelectedCourier->setSourceStockId($data['source_stock_id']);
            $modSelectedCourier->save();

            $selectedCourierId = $modSelectedCourier->getEntityId();

            if(!empty($selectedCourierId)){
                // save to omnyfy_easyship_salesorder_courier
                $modSalesOrderCourier = $this->_salesOrderCourier->create();
                $modSalesOrderCourier->setOrderId($data['order_id']);
                $modSalesOrderCourier->setVendorLocationId($data['vendor_location_id']);
                $modSalesOrderCourier->setVendorId($data['vendor_id']);
                $modSalesOrderCourier->setSelectedCourierId($selectedCourierId);
                $modSalesOrderCourier->setShipByMarketplace($data['ship_by_marketplace']);
                $modSalesOrderCourier->save();

                $salesOrderCourierId = $modSalesOrderCourier->getEntityId();

                foreach ($data['items_detail'] as $item){
                    // save to omnyfy_easyship_quoteitem_courier
                    $modQuoteItemCourier = $this->_quoteItemCourier->create();
                    $modQuoteItemCourier->setQuoteitemId($item['quote_item_id']);
                    $modQuoteItemCourier->setQuoteId($data['quote_id']);
                    $modQuoteItemCourier->setSelectedCourierId($selectedCourierId);
                    $modQuoteItemCourier->setShipByMarketplace($data['ship_by_marketplace']);
                    $modQuoteItemCourier->setEasyshipAccountId($data['easyship_account_id']);
                    $modQuoteItemCourier->setVendorLocationId($data['vendor_location_id']);
                    $modQuoteItemCourier->save();

                    if(!empty($salesOrderCourierId)){
                        // save to omnyfy_easyship_vendor_salesorderitem_courier
                        $modSalesOrderItemCourier = $this->_salesOrderItemCourier->create();
                        $modSalesOrderItemCourier->setItemId($item['order_item_id']);
                        $modSalesOrderItemCourier->setSalesOrderId($salesOrderCourierId);
                        $modSalesOrderItemCourier->setVendorId($data['vendor_id']);
                        $modSalesOrderItemCourier->save();
                    }
                }
            }

        } catch (\Exception $e) {
            $this->log($e->getMessage());
            $this->error($e->getMessage());
        }
        
    }

    /**
     * @param $msg
     * @param null $status
     */
    protected function error($msg, $status = null)
    {
        if ($status && $status > 0)
            $responseStatus = $status;
        else
            $responseStatus = 202;

        $this->_response
            ->setStatusCode($responseStatus)
            ->setContent($msg);

        $this->log("$responseStatus $msg");
    }

    /**
     * @param $msg
     */
    protected function log($msg)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/observer-placeorder.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($msg);
    }
}