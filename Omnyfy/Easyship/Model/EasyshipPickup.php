<?php
namespace Omnyfy\Easyship\Model;

class EasyshipPickup extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\Easyship\Model\ResourceModel\EasyshipPickup');
    }
}