<?php
/**
 * Project: Multi Vendor M2.
 * User: jing
 * Date: 17/7/17
 * Time: 11:57 AM
 */
namespace Omnyfy\Vendor\Block\Adminhtml\Shipment\Location;

use Magento\Framework\DataObject;

class OrderItems extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $orderRepository;
    protected $shippingHelper;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Backend\Block\Context $context,
        \Omnyfy\Vendor\Helper\Shipping $shippingHelper,
        array $data = [])
    {
        $this->orderRepository = $orderRepository;
        $this->shippingHelper = $shippingHelper;
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $locationId = $row->getEntityId();
        $orderId = $this->getRequest()->getParam('order_id');

        $order = $this->orderRepository->get($orderId);
        $content = '';
        $items = [];

        $calculate_by = $this->shippingHelper->getCalculateShippingBy();

        foreach($order->getItems() as $item) {
            if ($item->getQtyToShip() <= 0 ) {
                continue;
            }
            if ($calculate_by != 'overall_cart' && $locationId != $item->getLocationId()) {
                continue;
            }

            $items[$item->getId()] = $item->getQtyToShip();
            $content .= '<span>' . $item->getName() .' x ' . $item->getQtyToShip() . '</span>';
        }

        if (!empty($items)) {
            $input = '<input type="hidden" name="items" value="' . json_encode(($items)) . '" />';
            $content = '<div>' . $content . $input . '</div>';
        }

        return $content;
    }
}