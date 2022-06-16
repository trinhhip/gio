<?php
/**
 * Project: Vendor Subscription
 * User: jing
 * Date: 2019-07-10
 * Time: 15:34
 */
namespace Omnyfy\VendorSubscription\Observer;

use Magento\Framework\Exception\LocalizedException;

class VendorTypeFormValidation implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $formData = $observer->getData('form_data');

        if ((!isset($formData['role_plan']) || empty($formData['role_plan']))
            && (!isset($formData['vendor_type']['role_plan']) || empty($formData['vendor_type']['role_plan'])) ) {
            //Throw exception

            throw new LocalizedException(__('At least one role and plan combination need be assigned.'));
        } else {
            $rolePlan = $formData['role_plan'];
            $arrayPlan = [];
            foreach ($rolePlan as $value) {
                $planId = $value['plan_id'];
                $arrayPlan[$planId][] = $planId;
            }
            if(count($arrayPlan) < count($rolePlan)){
                throw new LocalizedException(__('A Subscription Plan can only be assigned to one Vendor Role. Please check your configuration and make the necessary changes.'));
            };
        }
    }
}
 