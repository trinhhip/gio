<?php


namespace OmnyfyCustomzation\Mcm\Plugin\Helper;


use Omnyfy\Mcm\Helper\Data;

class DataPlugin
{
    public function afterGetTemplateViewInvoice(Data $subject, $result){
        if ($subject->isEnable()) {
            return 'OmnyfyCustomzation_Mcm::order/invoice/view/items/renderer/default.phtml';
        }
        return $result;
    }
    public function afterGetTemplateItemsInvoice(Data $subject, $result){
        if ($subject->isEnable()) {
            return 'OmnyfyCustomzation_Mcm::order/invoice/view/items.phtml';
        }
        return $result;
    }
    public function afterGetTemplateNewInvoice(Data $subject, $result){
        if ($subject->isEnable()) {
            return 'OmnyfyCustomzation_Mcm::order/invoice/create/items/renderer/default.phtml';
        }
        return $result;
    }
}