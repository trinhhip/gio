<?php
    /**
    * Project: Rma Mobile API.
    * User: ab
    * Date: 2019-10-15
    * Time: 15:25
    */
namespace Omnyfy\RmaMobileapi\Model\Data;

class RmaOrderData implements \Omnyfy\RmaMobileapi\Api\Data\RmaOrderDataInterface
{
    protected $_data;
    
    /**
    * {@inheritdoc}
    */
    public function getData()
    {
        return $this->_data;
    }

    /**
    * {@inheritdoc}
    */
    public function setData($data)
    {
        $this->_data = $data;
    }

}
