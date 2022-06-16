<?php
namespace Omnyfy\Easyship\Model;

class EasyshipRateOption extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\Easyship\Model\ResourceModel\EasyshipRateOption');
    }

    public function getRateOptionByAccountId($accountId){
        $collection = $this->getCollection()
            ->addFieldToFilter('easyship_account_id', $accountId)
        ;
        if (count($collection) > 0) {
            return $collection->getFirstItem();
        }else{
            return null;
        }
    }
}