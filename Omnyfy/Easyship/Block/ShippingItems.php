<?php
namespace Omnyfy\Easyship\Block;

class ShippingItems extends \Magento\Shipping\Block\Items
{
    protected $shipFactory;
    protected $labelFactory;
    protected $selectedFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Omnyfy\Easyship\Model\EasyshipShipmentFactory $shipFactory,
        \Omnyfy\Easyship\Model\EasyshipShipmentLabelFactory $labelFactory,
        \Omnyfy\Easyship\Model\EasyshipSelectedCourierFactory $selectedFactory,
        array $data = []
    ){
        parent::__construct($context, $registry, $data);
        $this->shipFactory = $shipFactory;
        $this->labelFactory = $labelFactory;
        $this->selectedFactory = $selectedFactory;
    }

    public function getTrackingDetail($orderId, $sourceStockId, $quoteId){
        $selected = $this->selectedFactory->create()->getSelectedCourierByQuoteAndSourceStockId($quoteId, $sourceStockId);

        if ($selected != null && $selected->getCourierId()) {
            $courierEntityId = $selected->getEntityId();
            $shipModel =  $this->shipFactory->create()->getEasyshipShipmentIdByParams($orderId, $sourceStockId, $courierEntityId);

            if($shipModel != null){
                $label = $this->labelFactory->create()->getLabelByShipmentId($shipModel->getEasyshipShipmentId());

                if($label != null){
                    return $label;
                }
            }
        }
        return null;
    }
}
