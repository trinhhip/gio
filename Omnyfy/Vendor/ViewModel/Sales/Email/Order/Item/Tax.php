<?php

namespace Omnyfy\Vendor\ViewModel\Sales\Email\Order\Item;

use Omnyfy\Vendor\Api\Data\OrderItemTaxInterfaceFactory;
use Omnyfy\Vendor\Api\Data\OrderItemTaxInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Sales\Api\Data\OrderItemInterface;

class Tax implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * @var OrderItemTaxInterfaceFactory
     */
    protected $orderItemTaxInterfaceFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Init plugin
     *
     * @param OrderItemTaxInterfaceFactory $orderItemTaxInterfaceFactory
     * @param ResourceConnection $resourceConnection
     * @param DataObjectHelper $dataObjectHelper,
     */
    public function __construct(
        OrderItemTaxInterfaceFactory $orderItemTaxInterfaceFactory,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->orderItemTaxInterfaceFactory = $orderItemTaxInterfaceFactory;
        $this->resourceConnection = $resourceConnection;
        $this->dataObjectHelper = $dataObjectHelper;
    }
    /**
     * Get taxes for an item of order
     *
     * @param OrderItemInterface $order
     * @return void
     */
    public function getOrderItemTax(OrderItemInterface $orderItem)
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
                  ->from(
                      ['taxItem' => 'sales_order_tax_item']
                  )
                  ->join(
                      ['orderTax' => 'sales_order_tax'],
                      'taxItem.`tax_id` = orderTax.`tax_id`',
                      ['orderTax.tax_id','orderTax.code','orderTax.title'],
                  )
                  ->where('taxItem.item_id = ?', $orderItem->getId());
        $data = $connection->fetchAll($select);

        $taxes = [];

        foreach ($data as $row) {
            $tax = $this->orderItemTaxInterfaceFactory->create();

            $this->dataObjectHelper->populateWithArray(
                $tax,
                $row,
                OrderItemTaxInterface::class
            );

            $taxes[] = $tax;
        }

        return $taxes;
    }
}
