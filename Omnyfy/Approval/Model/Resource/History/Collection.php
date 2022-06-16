<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-20
 * Time: 15:54
 */
namespace Omnyfy\Approval\Model\Resource\History;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Omnyfy\Approval\Model\History', 'Omnyfy\Approval\Model\Resource\History');
    }
}
 