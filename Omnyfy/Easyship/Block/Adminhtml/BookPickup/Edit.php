<?php
namespace Omnyfy\Easyship\Block\Adminhtml\BookPickup;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $shipFactory;
    protected $labelFactory;
    protected $shipPickFactory;
    protected $pickupFactory;
    protected $orderRepo;
    protected $locationFactory;
    protected $apiHelper;
    protected $sourceCollectionFactory;
    protected $vSourceStockResource;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Omnyfy\Easyship\Model\EasyshipShipmentFactory $shipFactory,
        \Omnyfy\Easyship\Model\EasyshipShipmentLabelFactory $labelFactory,
        \Omnyfy\Easyship\Model\EasyshipShipmentPickupFactory $shipPickFactory,
        \Omnyfy\Easyship\Model\EasyshipPickupFactory $pickupFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepo,
        \Omnyfy\Vendor\Model\LocationFactory $locationFactory,
        \Omnyfy\Easyship\Helper\Api $apiHelper,
        \Omnyfy\Easyship\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->shipFactory = $shipFactory;
        $this->labelFactory = $labelFactory;
        $this->shipPickFactory = $shipPickFactory;
        $this->pickupFactory = $pickupFactory;
        $this->orderRepo = $orderRepo;
        $this->locationFactory = $locationFactory;
        $this->apiHelper = $apiHelper;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->vSourceStockResource = $vSourceStockResource;
    }

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_bookPickup';
        $this->_blockGroup = 'Omnyfy_Easyship';

        $this->buttonList->add(
            'book_pickup',
            [
                'id' => 'easyship_book_pickup',
                'label' => __('Book Pickup'),
                'class' => 'save'
            ]
        );
        $this->buttonList->add(
            'mark_handed_over',
            [
                'id' => 'mark_handed_over',
                'label' => __('Mark as Handed Over'),
                'class' => 'save'
            ]
        );
        $this->buttonList->add(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
                'class' => 'back'
            ],
            -1
        );
    }

    protected function _prepareLayout()
    {
        $courier = $this->getRequest()->getParam('courier');
        $location_id = $this->getRequest()->getParam('location');
        $courierName = 'Book';

        if (!($courier == '' || $location_id == 0)) {
            $coll = $this->getShipmentList($courier, $location_id);
            if (count($coll) > 0) {
                $courierName = $coll->getFirstItem()->getCourierName();
            }
        }
        $title = __($courierName. ' Pickup');
        $this->getLayout()->getBlock('page.title')->setPageTitle($title);
        return parent::_prepareLayout();
    }

    public function getShipmentList($courier, $sourceStockId){
        if ($courier == '' || $sourceStockId == 0) {
            return null;
        }
        $model = $this->shipFactory->create()->getShipmentListByCourierAndLocation($courier, $sourceStockId);
        return $model;
    }

    public function getOrder($orderId){
        $order = $this->orderRepo->get($orderId);
        return $order;
    }

    public function getSourceDetail($sourceStockId){

        $sourceCode = $this->vSourceStockResource->getSourceCodeById($sourceStockId);
        return $this->sourceCollectionFactory->create()->getItemById($sourceCode);
    }

    public function getPickupReferenceByShipmentId($easyshipShipmentId){
        $model = $this->shipPickFactory->create()->getPickupIdByShipmentId($easyshipShipmentId);
        $data = 'Pending';

        if (count($model) > 0) {
            $pickupId = $model->getFirstItem()->getPickupId();
            if ($pickupId) {
                $pickup = $this->pickupFactory->create()->load($pickupId);
                if($pickup->getPickupReferenceNumber()){
                    $data = $pickup->getPickupReferenceNumber();
                }else{
                    $data = ucfirst($pickup->getPickupState());
                }
            }
        }
        return $data;
    }

    public function getTrackingDetailByShipmentId($easyshipShipmentId){
        $label = $this->labelFactory->create()->getLabelByShipmentId($easyshipShipmentId);
        if($label != null){
            return $label;
        }
        return null;
    }
    
}
