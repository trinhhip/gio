<?php
namespace Omnyfy\Easyship\Ui\Component\Pickup;

class PickupGridDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $backendSession;
    
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Omnyfy\Easyship\Model\ResourceModel\EasyshipShipment\CollectionFactory $collectionFactory,
        \Magento\Backend\Model\Session $backendSession,
        array $meta = [],
        array $data = []
    ){
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->backendSession = $backendSession;
    }

    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $vendorInfo = $this->backendSession->getVendorInfo();
        $vendorId = isset($vendorInfo['vendor_id'])? $vendorInfo['vendor_id'] : null;

        $this->getCollection()->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->join(
                'omnyfy_vendor_source_stock',
                'main_table.source_stock_id = omnyfy_vendor_source_stock.id',
            )
            ->join(
                'inventory_source',
                'omnyfy_vendor_source_stock.source_code = inventory_source.source_code',
                ['name' => 'inventory_source.name']
            )
            ->join(
                'omnyfy_easyship_selected_courier',
                'main_table.selected_courier_id = omnyfy_easyship_selected_courier.entity_id',
                [
                    'courier_id' => 'omnyfy_easyship_selected_courier.courier_id'
                ]
            )
            ->columns(
                [
                    'courier_name',
                    'source_stock_id',
                    new \Zend_Db_Expr('COUNT(`easyship_shipment_id`) as count')
                ]
            )
            ->group(['courier_id', 'source_stock_id']);

        // if(!empty($vendorId) ){
        //     $this->getCollection()->getSelect()
        //         ->where('main_table.source_stock_id IN (?)', $vendorInfo['location_ids']);
        // }

        return [
            'totalRecords' => count($this->getCollection()->getData()),
            'items' => $this->getCollection()->getData()
        ];
    }

}
