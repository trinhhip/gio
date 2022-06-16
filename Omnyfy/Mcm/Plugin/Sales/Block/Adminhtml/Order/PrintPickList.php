<?php
namespace Omnyfy\Mcm\Plugin\Sales\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class PrintPickList
{
   public function beforeSetLayout(OrderView $subject)
   {
       $orderId = $subject->getOrderId();
       $subject->addButton(
           'order_print_picklist',
           [
               'label' => __('Print Pick List'),
               'class' => __('pick-list'),
               'id' => 'order-view-print-picklist',
               'onclick' => 'setLocation(\'' . $subject->getUrl('omnyfy_mcm/salesorder/picklist', ['order_id' => $orderId]) . '\')'
           ]
       );
   }
}