<?php
namespace Omnyfy\Easyship\Model;

class EasyshipAccount extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\Easyship\Model\ResourceModel\EasyshipAccount');
    }

    public function getDefaultMOAccount(){
        $collection = $this->getCollection()
            ->addFieldToSelect('access_token')
            ->addFieldToFilter('created_by_mo', 1)
        ;
        if (count($collection) > 0) {
            return $collection->getFirstItem();
        }else{
            return null;
        }
    }

    public function getAccountListByVendorAndCountry($vendorLocationId, $country){
        $collection = $this->getCollection()
        ->addFieldToFilter('country_code', $country)
        ->addFieldToFilter(
            ['created_by', 'created_by_mo'],
            [
                ['eq' => $vendorLocationId],
                ['eq' => 1]
            ]
        );
        return $collection;
    }
}