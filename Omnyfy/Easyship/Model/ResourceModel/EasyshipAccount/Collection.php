<?php
namespace Omnyfy\Easyship\Model\ResourceModel\EasyshipAccount;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ){
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct()
    {
        $this->_init('Omnyfy\Easyship\Model\EasyshipAccount', 'Omnyfy\Easyship\Model\ResourceModel\EasyshipAccount');
    }

    protected function _initSelect() {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            'omnyfy_easyship_rate_option',
            'main_table.entity_id = omnyfy_easyship_rate_option.easyship_account_id',
            [
                'fixed_rate' => 'omnyfy_easyship_rate_option.shipping_rate_option_price',
                'fixed_rate_name' => 'omnyfy_easyship_rate_option.name'
            ]
        );
    }
}
