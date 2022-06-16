<?php
namespace Omnyfy\Mcm\Block\Adminhtml\Items\Renderer;

class DefaultRenderer extends \Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer
{
    public function getOrder()
    {
        if (!empty($this->getItem()->getShipment())) {
            return $this->getItem()->getShipment()->getOrder();
        }

        return parent::getOrder();
    }
}