<?php
/**
 * Project: Omnyfy Multi Vendor.
 * User: jing
 * Date: 1/5/17
 * Time: 1:43 PM
 */
namespace Omnyfy\Vendor\Plugin\Quote\Model\Quote\Item\QuantityValidator\Initializer;

class Option
{
    protected $_extraHelper;

    public function __construct(
        \Omnyfy\Vendor\Helper\Extra $extraHelper
    ) {
        $this->_extraHelper = $extraHelper;
    }

    public function aroundInitialize(
        $subject,
        callable $proceed,
        \Magento\Quote\Model\Quote\Item\Option $option,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        $qty)
    {
         $result = $proceed($option, $quoteItem, $qty);
         $stockItem = $subject->getStockItem($option, $quoteItem);
         if ($stockItem->hasLocationId()) {
             $quoteItem->setData('location_id', $stockItem->getLocationId());
             $quoteItem->setData('source_stock_id', $stockItem->getLocationId());
             $this->_extraHelper->updateAddressItemLocationId($quoteItem->getId(), $stockItem->getLocationId());
         }
         elseif ($stockItem->hasQtys()) {
             foreach($stockItem->getQtys() as $locationId => $stockQty) {
                 if (!$stockItem->getManageStock()) {
                     $stockItem->setData('location_id', $locationId);
                     $quoteItem->setData('source_stock_id', $locationId);
                     $this->_extraHelper->updateAddressItemLocationId($quoteItem->getId(), $locationId);
                     break;
                 }
                 if ($stockQty - $stockItem->getMinQty() - $qty >= 0) {
                     $stockItem->setData('location_id', $locationId);
                     $quoteItem->setData('source_stock_id', $locationId);
                     $this->_extraHelper->updateAddressItemLocationId($quoteItem->getId(), $locationId);
                     break;
                 }
             }
         }
         if ($stockItem->hasVendorId()) {
             $quoteItem->setData('vendor_id', $stockItem->getVendorId());
         }
         $sessionVendorId = $this->_extraHelper->getSessionVendorId($quoteItem->getQuote());
         // Change vendor_id quote_item
         if (!empty($sessionVendorId) && !$stockItem->hasVendorId()) {
             $quoteItem->setData('vendor_id', $sessionVendorId);
             $stockItem->setData('session_vendor_id', $sessionVendorId);
             $stockItem->setData('vendor_id', $sessionVendorId);
         }
         return $result;
    }
}
