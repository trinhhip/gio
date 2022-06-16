<?php
namespace Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement\Grid;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable = 'omnyfy_spotlight_banner_placement',
        $resourceModel = 'Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement',
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
                ['bv' => $this->getTable('omnyfy_spotlight_banner_vendor')],
                'main_table.banner_id = bv.banner_id',
                [
                    'ad_spots' => new \Zend_Db_expr('COUNT(DISTINCT bv.banner_vendor_id)'),
                ]
            )
            ->joinLeft(
                ['click' => $this->getTable('omnyfy_spotlight_clicks')],
                'bv.banner_vendor_id = click.banner_vendor_id',
                [
                    'total_clicks' => new \Zend_Db_expr('COUNT(DISTINCT click.click_id)'),
                ]
            )
            ->group(['main_table.banner_id']);
        ;

        $this->addFilterToMap('banner_id', 'main_table.banner_id');
    }
}