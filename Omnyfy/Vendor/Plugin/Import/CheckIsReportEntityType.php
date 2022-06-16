<?php

namespace Omnyfy\Vendor\Plugin\Import;

class CheckIsReportEntityType
{
    public function afterIsReportEntityType($subject, $result, $entity = null)
    {
        if($entity == 'stock_sources'){
            return true;
        }
        return $result;
    }
}
