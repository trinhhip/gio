<?php
namespace Omnyfy\Easyship\Model;

class EasyshipVendorLocation extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\Easyship\Model\ResourceModel\EasyshipVendorLocation');
    }

    public function getLocationAccount($vendorLocationId){
        $collection = $this->getCollection()
            ->join(
                'omnyfy_easyship_account',
                'main_table.easyship_account_id = omnyfy_easyship_account.entity_id',
                [
                    'name' => 'omnyfy_easyship_account.name',
                    'access_token' => 'omnyfy_easyship_account.access_token',
                    'use_live_rate' => 'omnyfy_easyship_account.use_live_rate',
                    'created_by_mo' => 'omnyfy_easyship_account.created_by_mo',
                ]
            )
            ->addFieldToFilter('main_table.vendor_location_id', $vendorLocationId)
        ;
        if (count($collection) > 0) {
            return $collection->getFirstItem();
        }else{
            return null;
        }
    }

    public function getAccountRateOptionByLocation($locationId){
        $collection = $this->getCollection()
            ->join(
                'omnyfy_easyship_account',
                'main_table.easyship_account_id = omnyfy_easyship_account.entity_id',
                [
                    'name' => 'omnyfy_easyship_account.name',
                    'access_token' => 'omnyfy_easyship_account.access_token',
                    'use_live_rate' => 'omnyfy_easyship_account.use_live_rate',
                    'created_by_mo' => 'omnyfy_easyship_account.created_by_mo',
                ]
            )
            ->join(
                'omnyfy_easyship_rate_option',
                'omnyfy_easyship_account.entity_id = omnyfy_easyship_rate_option.easyship_account_id',
                [
                    'name_rate_option' => 'omnyfy_easyship_rate_option.name',
                    'active_rate_option' => 'omnyfy_easyship_rate_option.is_active',
                    'price_rate_option' => 'omnyfy_easyship_rate_option.shipping_rate_option_price',
                    'shipping_rate_option_id' => 'omnyfy_easyship_rate_option.shipping_rate_option_id',
                ]
            )
            ->addFieldToFilter('main_table.vendor_location_id', $locationId)
            ->addFieldToFilter('omnyfy_easyship_rate_option.is_active', true)
        ;
        if (count($collection) > 0) {
            return $collection->getFirstItem();
        }else{
            return null;
        }
    }
}
