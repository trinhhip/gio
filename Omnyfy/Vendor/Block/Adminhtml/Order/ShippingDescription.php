<?php
/**
 * Project: Multi Vendor M2.
 * User: jing
 * Date: 17/7/17
 * Time: 4:23 PM
 */

namespace Omnyfy\Vendor\Block\Adminhtml\Order;


use Magento\Framework\View\Element\Template;

class ShippingDescription extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Omnyfy\Vendor\Helper\Data
     */
    private $vendorHelper;
    /**
     * @var \Omnyfy\Mcm\Model\ResourceModel\FeesManagement
     */
    private $feesManagement;
    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $_adminHelper;

    public function __construct(
        \Omnyfy\Vendor\Helper\Data $vendorHelper,
        \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $feesManagement,
        \Magento\Sales\Helper\Admin $adminHelper,
        Template\Context $context,
        array $data = [])
    {
        $this->vendorHelper = $vendorHelper;
        $this->feesManagement = $feesManagement;
        $this->_adminHelper = $adminHelper;
        parent::__construct($context, $data);
    }
    public function getOrderShippingInformation($order, $invoice){
        $vendorOrderTotal = [];
        $vendorOrderTotal['is_mo'] = true;
        $shippingData = $this->vendorHelper->getShippingData($order, $invoice);
        if(!empty($shippingData['vendor_info'])){
            $vendorOrderTotal = $this->feesManagement->getVendorInvoiceTotals($shippingData['vendor_info']['vendor_id'], $invoice->getId());
            $vendorOrderTotal['is_mo'] = false;
        }
        $vendorOrderTotal['shipping_title'] = $shippingData['title'];
        return $vendorOrderTotal;
    }

    public function displayPrices($order, $basePrice, $price, $strong = false, $separator = '<br/>')
    {
        return $this->_adminHelper->displayPrices(
            $order,
            $basePrice,
            $price,
            $strong,
            $separator
        );
    }

}