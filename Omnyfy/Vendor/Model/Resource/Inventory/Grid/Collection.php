<?php

/**
 * Project: Vendor.
 * User: jing
 * Date: 25/1/18
 * Time: 12:17 PM
 */

namespace Omnyfy\Vendor\Model\Resource\Inventory\Grid;

class Collection extends \Omnyfy\Vendor\Model\Resource\Inventory\Collection
implements \Magento\Framework\Api\Search\SearchResultInterface
{
    protected $entityAttributeFactory;
    private $expressionFieldsToSelect = [];

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        \Magento\Eav\Model\Entity\AttributeFactory $entityAttributeFactory
    ) {
        $this->entityAttributeFactory = $entityAttributeFactory;
        $this->_eventManager = $eventManager;
        $this->_construct();
        $this->_resource = $resource;
        $this->setConnection($this->getResource()->getConnection());
        $this->_initSelect();
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    public function _construct()
    {
        $this->_init(
            'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
            'Omnyfy\Vendor\Model\Resource\Inventory'
        );
    }

    public function _initSelect()
    {
        parent::_initSelect();

        $productName = $this->entityAttributeFactory->create()->loadByCode('catalog_product', 'name');
        $productStatus = $this->entityAttributeFactory->create()->loadByCode('catalog_product', 'status');

        $this->getSelect()->join(
            ['product' => $this->getTable('catalog_product_entity')],
            'main_table.product_id = product.entity_id',
            ['sku' => 'product.sku', 'type_id' => 'product.type_id']
        )
        ->joinLeft(
            ['pv' => $this->getTable('catalog_product_entity_varchar')],
            'pv.entity_id=product.entity_id AND pv.store_id=0 AND pv.attribute_id=' . $productName->getId(),
            ['product_name' => 'pv.value']
        )
        ->joinLeft(
            ['source_stock' => $this->getTable('omnyfy_vendor_source_stock')],
            'main_table.source_stock_id = source_stock.id'
        );
        $this->getSelect()->joinLeft(
            ['pi' => 'catalog_product_entity_int'],
            'pi.entity_id = product.entity_id AND pi.store_id = 0 AND pi.attribute_id = ' . $productStatus->getId(),
            ['enabled' => 'pi.value']
        );
        $this->getSelect()->joinLeft(
            ['is' => 'inventory_source'],
            'is.source_code = source_stock.source_code',
            ['source_name' => 'is.name', 'source_code' => 'is.source_code']
        );
        $this->getSelect()->joinLeft(
            ['ist' => 'inventory_stock'],
            'ist.stock_id = source_stock.stock_id',
            ['stock_name' => 'ist.name']
        );
        $this->addFilterToMap('source_code', 'is.source_code');
        $this->addFilterToMap('sku', 'product.sku');
        $this->addFilterToMap('product_name', 'pv.value');
        $this->addFilterToMap('stock_name', 'ist.name');

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
