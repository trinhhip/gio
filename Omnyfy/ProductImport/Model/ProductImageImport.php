<?php
namespace Omnyfy\ProductImport\Model;

class ProductImageImport extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\ProductImport\Model\ResourceModel\ProductImageImport');
    }

    private function getUrlHash($sku, $image_url){
        return md5($sku."-".$image_url);
    }

    public function getImage($sku, $image_url){
        $urlHash = $this->getUrlHash($sku, $image_url);
        $collection = $this->getCollection()
            ->addFieldToFilter('url_hash', $urlHash)
        ;

        if (count($collection) > 0) {
            return $collection->getFirstItem();
        }else{
            return null;
        }
    }
}
