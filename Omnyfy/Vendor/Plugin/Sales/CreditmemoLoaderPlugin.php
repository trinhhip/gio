<?php
namespace Omnyfy\Vendor\Plugin\Sales;

class CreditmemoLoaderPlugin
{
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function afterLoad($subsject, $result) {
        $savedDataCreditmemo = $this->getItemData($subsject);
        $backToStockProductIds = [];
        foreach ($savedDataCreditmemo as $orderItemId => $itemData) {
            if (isset($itemData['back_to_stock'])) {
                $backToStockProductIds[] = $orderItemId;
            }
        }

        $this->registry->register('back_to_stock_product', $backToStockProductIds);
        return $result;
    }

    public function getItemData($subsject) {
        $data = $subsject->getCreditmemo();
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = [];
        }

        return $qtys;
    }
}