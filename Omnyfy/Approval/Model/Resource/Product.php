<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-19
 * Time: 15:16
 */
namespace Omnyfy\Approval\Model\Resource;

class Product extends \Omnyfy\Core\Model\ResourceModel\AbstractDbModel
{
    protected function _construct()
    {
            $this->_init('omnyfy_approval_product', 'id');
    }

    protected function getUpdateFields()
    {
        return [
            'status'
        ];
    }
}
 