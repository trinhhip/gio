<?php
namespace Omnyfy\Easyship\Model;

class EasyshipShipmentItem extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\Easyship\Model\ResourceModel\EasyshipShipmentItem');
    }
}