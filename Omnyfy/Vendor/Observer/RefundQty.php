<?php
/**
 * Project: Multi Vendor.
 * User: jing
 * Date: 9/11/18
 * Time: 10:09 PM
 */
namespace Omnyfy\Vendor\Observer;

class RefundQty implements \Magento\Framework\Event\ObserverInterface
{
    protected $_vendorResource;
    protected $vSourceStockResource;
    protected $registry;

    public function __construct(
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource,
        \Magento\Framework\Registry $registry
    )
    {
        $this->_vendorResource = $vendorResource;
        $this->vSourceStockResource = $vSourceStockResource;
        $this->registry = $registry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditMemo = $observer->getCreditmemo();
        $items = $creditMemo->getAllItems();
        $data = [];
        $backToStockProductIds = $this->registry->registry('back_to_stock_product');

        foreach($items as $item) {

            $orderItem = $item->getOrderItem();
            $isbackToStock = (in_array($orderItem->getProductId(), $backToStockProductIds)) ? 1 : 0;
            $sourceStockId = $orderItem->getSourceStockId();
            $sourceCode = $this->vSourceStockResource->getSourceCodeById($sourceStockId);
            $data[] = [
                'product_id' => $orderItem->getProductId(),
                'source_code' => $sourceCode,
                'qty' => $item->getQty(),
                'is_back_to_stock' => $isbackToStock
            ];
        }

        if (empty($data) && $creditMemo->getInvoice()) {
            $items = $creditMemo->getInvoice()->getAllItems();

            foreach($items as $item) {
                $sourceStockId = $item->getSourceStockId();
                $sourceCode = $this->vSourceStockResource->getSourceCodeById($sourceStockId);
                $isbackToStock = (in_array($item->getProductId(), $backToStockProductIds)) ? 1 : 0;

                $data[] = [
                    'product_id' => $item->getProductId(),
                    'source_code' => $sourceCode,
                    'qty' => $item->getQty(),
                    'is_back_to_stock' => $isbackToStock
                ];
            }
        }

        $this->_vendorResource->returnQty($data);
    }
}