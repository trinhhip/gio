<?php
namespace Omnyfy\Easyship\Model;

class EasyshipShipmentPickup extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\Easyship\Model\ResourceModel\EasyshipShipmentPickup');
    }

    public function getPickupIdByShipmentId($easyshipShipmentId){
        $collection = $this->getCollection()
            ->addFieldToFilter('easyship_shipment_id', $easyshipShipmentId)
        ;

        return $collection;
    }
}