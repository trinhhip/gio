<?php
namespace Omnyfy\Rma\Helper\Item;

/**
 * Helper which creates different html code
 */
class Html extends \Mirasvit\Rma\Helper\Item\Html
{

    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface
     */
    private $itemManagement;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        $this->itemManagement = $itemManagement;
        $this->orderRepository = $orderRepository;
        parent::__construct($itemManagement, $orderRepository, $context);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getItemLabel($item)
    {
        try {
            $orderItem = $this->itemManagement->getOrderItem($item);
            $name = $orderItem->getName();
            $options = $this->getItemOptions($orderItem);
            if (count($options)) {
                $name .= ' (';
                foreach ($options as $option) {
                    $name .= $option['label'] . ': ' . $option['value'] . ', ';
                }
                $name = substr($name, 0, -2); //remove last ,
                $name .= ')';
            }
            return $name;
        } catch (\Exception $e) {
            return;
        }

    }

}
