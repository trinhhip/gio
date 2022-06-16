<?php
namespace Omnyfy\Order\Helper;
use \Magento\Framework\Exception\CouldNotSaveException;

class ShipmentApi extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $orderItemRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
    ) {
        parent::__construct($context);
        $this->orderItemRepository = $orderItemRepository;
    }

    public function validateMultipleVendorItems($shipmentItems, $vendorId){
        $isIntegrationToken = false;
        foreach ($shipmentItems as $item) {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
            if ($vendorId === 0) {
                //set vendor_id for shipment from order item
                $vendorId = $orderItem->getVendorId();
                $isIntegrationToken = true;
            }

            if ($vendorId != $orderItem->getVendorId()) {
                if ($isIntegrationToken) {
                    throw new CouldNotSaveException(
                        __(__("Cannot create shipment with items from multiple vendor."))
                    );
                }else{
                    throw new CouldNotSaveException(
                        __(__("Cannot ship other vendor's item."))
                    );
                }
            }
        }
    }

}
