<?php
namespace Omnyfy\Easyship\Model;

class EasyshipShipmentLabel extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\Easyship\Model\ResourceModel\EasyshipShipmentLabel');
    }

    public function getLabelByShipmentId($easyshipShipmentId){
        $collection = $this->getCollection()
            ->addFieldToFilter('easyship_shipment_id', $easyshipShipmentId)
        ;
        if (count($collection) > 0) {
            return $collection->getFirstItem();
        }else{
            return null;
        }
    }
}
