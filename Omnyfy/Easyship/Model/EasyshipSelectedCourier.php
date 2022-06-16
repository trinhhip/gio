<?php
namespace Omnyfy\Easyship\Model;

class EasyshipSelectedCourier extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\Easyship\Model\ResourceModel\EasyshipSelectedCourier');
    }

    public function getSelectedCourierByQuoteAndSourceStockId($quoteId, $sourceStockId){
        $collection = $this->getCollection()
            ->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('source_stock_id', $sourceStockId)
        ;
        if (count($collection) > 0) {
            return $collection->getFirstItem();
        }else{
            return null;
        }
    }
}
