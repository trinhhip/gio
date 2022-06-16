<?php
namespace Omnyfy\VendorFeatured\Model\ResourceModel\PromoVendorWidget\Grid;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable = 'omnyfy_vendorfeatured_promo_widget',
        $resourceModel = 'Omnyfy\VendorFeatured\Model\ResourceModel\PromoVendorWidget',
        $identifierName = null,
        $connectionName = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel, $identifierName, $connectionName);
    }

    /**
     * @return Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['ve' => $this->getTable('omnyfy_vendor_vendor_entity')],
            'main_table.vendor_id = ve.entity_id',
            [
                'vendor_name' => 've.name',
            ]
        );
    }
}
