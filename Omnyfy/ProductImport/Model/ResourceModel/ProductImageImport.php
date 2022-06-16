<?php
namespace Omnyfy\ProductImport\Model\ResourceModel;

class ProductImageImport extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ){
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_productimport_productimage_cache', 'id');
    }
}
