<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-19
 * Time: 15:29
 */
namespace Omnyfy\Approval\Model\Resource\Product;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('Omnyfy\Approval\Model\Product', 'Omnyfy\Approval\Model\Resource\Product');
    }
}