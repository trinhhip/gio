<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-20
 * Time: 15:45
 */
namespace Omnyfy\Approval\Model\Resource;

class History extends \Omnyfy\Core\Model\ResourceModel\AbstractDbModel
{
    protected function _construct()
    {
        $this->_init('omnyfy_approval_product_history', 'history_id');
    }

    protected function getUpdateFields()
    {
        return [
            'product_id',
            'comment'
        ];
    }

}
 