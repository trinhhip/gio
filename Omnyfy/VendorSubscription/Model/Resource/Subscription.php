<?php
/**
 * Project: Vendor Subscription
 * User: jing
 * Date: 2019-07-01
 * Time: 17:41
 */
namespace Omnyfy\VendorSubscription\Model\Resource;

class Subscription extends \Omnyfy\Core\Model\ResourceModel\AbstractDbModel
{
    protected function _construct()
    {
        $this->_init('omnyfy_vendorsubscription_subscription', 'id');
    }

    protected function getUpdateFields()
    {
        return [
            'vendor_name',
            'plan_id',
            'plan_name',
            'plan_price',
            'billing_interval',
            'status',
            'gateway_id',
            'vendor_type_id',
            'role_id',
            'show_on_front',
            'next_billing_at',
            'cancelled_at',
            'expiry_at'
        ];
    }

    public function isSubscriptionForVendor($subscriptionId, $vendorId) {
        $conn = $this->getConnection();

        $table = $this->getMainTable();

        $select = $conn->select()->from(
            $table,
            ['cnt' => 'COUNT(*)']
        )
            ->where("id = ?", $subscriptionId)
            ->where("vendor_id = ?", $vendorId)
        ;

        $cnt = $conn->fetchOne($select);

        return $cnt > 0 ? true: false;
    }

    public function getVendorActiveAssignedByPlanId($planId) {
        if (empty($planId)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()
                    ->from($this->getMainTable(), 'vendor_id')
                    ->joinInner(
                        'omnyfy_vendor_vendor_entity', 
                        'omnyfy_vendorsubscription_subscription.vendor_id = omnyfy_vendor_vendor_entity.entity_id AND omnyfy_vendor_vendor_entity.status = 1', 
                        'omnyfy_vendorsubscription_subscription.vendor_id')
                    ->where('plan_id = ?', $planId);
        $a = $query->__toString();
        $rows = $conn->fetchCol($query);
        if (!empty($rows)) {
            return true;
        } else {
            return false;
        }    
    }
}
 