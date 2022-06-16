<?php
/**
 * Project: Omnyfy Multi Vendor.
 * User: jing
 * Date: 26/4/17
 * Time: 4:17 PM
 */

namespace Omnyfy\Vendor\Plugin;

use Magento\Framework\App\RequestInterface;

class StockRegistryProvider
{
    protected $helper;

    protected $state;

    protected $notCheckTypes = [
        \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
        'booking',
    ];

    protected $request;

    public function __construct(
        \Omnyfy\Vendor\Helper\Data $helper,
        \Magento\Framework\App\State $state,
        RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->_state = $state;
        $this->request = $request;
    }

    public function aroundGetStockItem(
            \Magento\CatalogInventory\Model\StockRegistryProvider $subject,
            callable $proceed,
            $productId,
            $scopeId
        )
    {
        //2017-09-16 22:39 Jing Xiao,
        //scopeId should be website id, but magento return 0 as default scope id
        //should always check with specified website id.
        //$scopeId = empty($scopeId) ? 1 : $scopeId;
        $stockItem = $proceed($productId, $scopeId);

        $vendorId = null;
        //2018-06-01 20:53 Jing Xiao
        //ignore bundle and configurable products only, any new type of products should check
        if (!in_array($stockItem->getTypeId(), $this->notCheckTypes) && !$stockItem->hasData('qtys')) {
            $qtys = $this->helper->groupInventoryByLocationId($stockItem->getProductId(), $scopeId, $vendorId);
            if (!empty($qtys)) {

                // Get the posted product data from product form save when in admin
                if ($this->_state->getAreaCode() == 'adminhtml') {
                    $productData = $this->request->getParam('product');
                    if (isset($productData['quantity_and_stock_status']) && isset($productData['quantity_and_stock_status']['qty']) && $productData['quantity_and_stock_status']['qty'] !== '') {
                        $stockItem->setData('qty', $productData['quantity_and_stock_status']['qty']);
                        if ($productData['quantity_and_stock_status']['is_in_stock'] == 0) {
                            $stockItem->setData('qty', '0');
                        }
                        $stockItem->setData('is_in_stock', $productData['quantity_and_stock_status']['is_in_stock']);
                    }
                    // This condition make MO/Vendor can not Replacement Order from RMA
                    // if($this->request->getControllerName() == 'order_create'){
                    //     $stockItem->setData('qtys', $qtys);
                    // }
                    $stockItem->setData('qtys', $qtys);
                }else{
                    $stockItem->setData('qtys', $qtys);
                }
            }
        }
        if (!is_null($vendorId) && !$stockItem->hasData('vendor_id')) {
            $stockItem->setData('vendor_id', $vendorId);
        }
        $sessionVendorId = $stockItem->getSessionVendorId();
        if (!empty($sessionVendorId) && (empty($stockItem->getVendorId()))) {
            $stockItem->setData('vendor_id', $sessionVendorId);
        }

        return $stockItem;
    }
}
