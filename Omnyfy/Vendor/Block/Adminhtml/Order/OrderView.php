<?php
namespace Omnyfy\Vendor\Block\Adminhtml\Order;

class OrderView extends \Magento\Framework\View\Element\Template
{
    protected $backendSession;
    protected $order;
    protected $locationFactory;
    protected $priceHelper;
    protected $vSourceStockResource;
    protected $vendorHelper;
    /**
     * @var \Omnyfy\Mcm\Model\ResourceModel\FeesManagement
     */
    private $feeManagement;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Sales\Api\OrderRepositoryInterface $order,
        \Omnyfy\Vendor\Helper\Data $vendorHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $feesManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->backendSession = $backendSession;
        $this->order = $order;
        $this->vendorHelper = $vendorHelper;
        $this->priceHelper = $priceHelper;
        $this->feeManagement = $feesManagement;
    }

    public function getShippingInfo($orderId){
        $order = $this->order->get($orderId);
        $quoteId = $order->getQuoteId();
        $vendorInfo = $this->backendSession->getVendorInfo();
        $result = [];
        $i = 0;
        $shippingInfo = $this->vendorHelper->getQuoteShippingInfo($quoteId);
        foreach ($shippingInfo as $data) {
            if (isset($vendorInfo['vendor_id'])) {
                #if vendor logged in, show only their select courier button
                if ($vendorInfo['vendor_id'] == $data['vendor_id']) {
                    $vendorOrderInfo = $this->feeManagement->getVendorOrderTotals($vendorInfo['vendor_id'],$orderId);
                    $sourceCode = $this->vendorHelper->getSourceCodeById($data['source_stock_id']);
                    $source = $this->vendorHelper->getSourceBySourceCode($sourceCode);
                    $data['source_stock_id'] = $data['source_stock_id'];
                    $data['vendor_id'] = $data['vendor_id'];
                    $data['vendor_name'] = $this->vendorHelper->getVendorNameById($data['vendor_id']);
                    $data['source_name'] = $source->getData('name');
                    $data['shipping_method'] = $data['carrier'];
                    if (isset($vendorOrderInfo['shipping_amount'])) {
                        $data['price'] = $this->priceHelper->currency($vendorOrderInfo['shipping_amount'], true, false);
                    } else {
                        $data['price'] = $this->priceHelper->currency($data['amount'], true, false);
                    }
                    
                    $result[] = $data;
                }
            }else{
                $sourceCode = $this->vendorHelper->getSourceCodeById($data['source_stock_id']);
                $source = $this->vendorHelper->getSourceBySourceCode($sourceCode);
                $result[$i]['source_stock_id'] = $data['source_stock_id'];
                $result[$i]['vendor_id'] = $data['vendor_id'];
                $result[$i]['vendor_name'] = $this->vendorHelper->getVendorNameById($data['vendor_id']);
                $result[$i]['source_name'] = $source->getData('name');
                $result[$i]['shipping_method'] = $data['carrier'];
                $result[$i]['price'] = $this->priceHelper->currency($data['amount'], true, false);
                $i++;
            }
        }

        return $result;

    }

}
