<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-20
 * Time: 15:43
 */
namespace Omnyfy\Approval\Model;

class History extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\Approval\Model\Resource\History');
    }
}
 