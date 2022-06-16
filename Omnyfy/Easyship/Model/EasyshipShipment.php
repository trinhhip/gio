<?php
namespace Omnyfy\Easyship\Model;

class EasyshipShipment extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\Easyship\Model\ResourceModel\EasyshipShipment');
    }

    public function getShipmentListByCourierAndLocation($courier_id, $sourceStockId){
        $collection = $this->getCollection()
            ->join(
                'omnyfy_easyship_shipment_item',
                'main_table.easyship_shipment_id = omnyfy_easyship_shipment_item.easyship_shipment_id',
                [
                    'count_items' => 'COUNT(main_table.easyship_shipment_id)',
                ]
            )
            ->join(
                'omnyfy_easyship_selected_courier',
                'main_table.selected_courier_id = omnyfy_easyship_selected_courier.entity_id',
                [
                    'courier_id' => 'omnyfy_easyship_selected_courier.courier_id'
                ]
            )
            ->addFieldToFilter('courier_id', $courier_id)
            ->addFieldToFilter('main_table.source_stock_id', $sourceStockId)
            ->setOrder('main_table.created_at','DESC');
        ;
        $collection->getSelect()->group('main_table.easyship_shipment_id');

        return $collection;
    }

    public function getEasyshipShipmentIdByParams($orderId, $sourceStockId, $selectedCourierId){
        $collection = $this->getCollection()
            ->addFieldToFilter('main_table.order_id', $orderId)
            ->addFieldToFilter('main_table.source_stock_id', $sourceStockId)
            ->addFieldToFilter('main_table.selected_courier_id', $selectedCourierId)
        ;
        $collection->setOrder('created_at', 'DESC');

        if (count($collection) > 0) {
            return $collection->getFirstItem();
        }else{
            return null;
        }
    }
}