<?php
namespace Omnyfy\VendorFeatured\Model\ResourceModel\VendorSpotlight\Grid;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable = 'omnyfy_spotlight_banner_vendor',
        $resourceModel = 'Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerVendor',
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

        $this->getSelect()
            ->joinLeft(
                ['click' => $this->getTable('omnyfy_spotlight_clicks')],
                'main_table.banner_vendor_id = click.banner_vendor_id',
                [
                    'total_clicks' => new \Zend_Db_expr('COUNT(click_id)')

                ]
            )
            ->joinLeft(
                ['ve' => $this->getTable('omnyfy_vendor_vendor_entity')],
                'main_table.vendor_id = ve.entity_id',
                [
                    'vendor_name' => 've.name',
                ]
            )
            ->group(['main_table.vendor_id']);
        ;
        $this->addFilterToMap('vendor_name', 've.name');

    }
}
