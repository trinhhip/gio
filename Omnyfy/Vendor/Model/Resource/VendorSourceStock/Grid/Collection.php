<?php
namespace Omnyfy\Vendor\Model\Resource\VendorSourceStock\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;

class Collection extends \Omnyfy\Vendor\Model\Resource\VendorSourceStock\Collection implements SearchResultInterface
{

    private $expressionFieldsToSelect = [];
    protected $request;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_eventManager = $eventManager;
        $this->_construct();
        $this->_resource = $resource;
        $this->setConnection($this->getResource()->getConnection());
        $this->_initSelect();
        $this->request = $request;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    public function _construct()
    {
        $this->_init(
            'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
            'Omnyfy\Vendor\Model\Resource\VendorSourceStock'
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['ist' => 'inventory_stock'],
            'main_table.stock_id = ist.stock_id',
            ['stock_name' => 'ist.name']
        );

        return $this;
    }

    public function getAggregations()
    {
        return $this->aggregations;
    }

    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    public function getSearchCriteria()
    {
        return null;
    }

    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    public function getTotalCount()
    {
        return $this->getSize();
    }

    public function setTotalCount($totalCount)
    {
        return $this;
    }

    public function setItems(array $items = null)
    {
        return $this;
    }
}
