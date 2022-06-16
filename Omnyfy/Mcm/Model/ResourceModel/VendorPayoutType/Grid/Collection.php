<?php
namespace Omnyfy\Mcm\Model\ResourceModel\VendorPayoutType\Grid;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function _initSelect()
    {
        $this->addFilterToMap('id', 'main_table.id');
        parent::_initSelect();
        $conn = $this->getConnection();
        $vendorTable = $conn->getTableName("omnyfy_vendor_vendor_entity");
        $payoutTypeTable = $conn->getTableName("omnyfy_mcm_payout_type");
        $kycTable = $conn->getTableName("omnyfy_vendor_kyc_details");
        $payoutTable = $conn->getTableName("omnyfy_mcm_vendor_payout");
        $this->getSelect()->join(
            ["vendor" => $vendorTable],
            'vendor.entity_id = main_table.vendor_id',
            ['vendor_name' => 'name']
        )->join(
            ["type" => $payoutTypeTable],
            'type.id = main_table.payout_type_id',
            ['payout_type']
        )->join(
            ["kyc" => $kycTable],
            'kyc.vendor_id = main_table.vendor_id',
            ['kyc_status']
        )->joinLeft(
            ["payout" => $payoutTable],
            'main_table.vendor_id = payout.vendor_id',
            ['account_ref']
        );
        $this->addFilterToMap('vendor_name', 'vendor.name');
        return $this;
    }
}