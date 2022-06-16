<?php
namespace Omnyfy\Easyship\Model\ResourceModel\Source;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\Inventory\Model\Source as SourceModel;
use Magento\InventoryApi\Model\SourceCarrierLinkManagementInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Psr\Log\LoggerInterface;

class Collection extends \Magento\Inventory\Model\ResourceModel\Source\Collection 
{
    private $sourceCarrierLinkManagement;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        SourceCarrierLinkManagementInterface $sourceCarrierLinkManagement,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->sourceCarrierLinkManagement = $sourceCarrierLinkManagement;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $sourceCarrierLinkManagement, $connection, $resource);
    }

    public function getSourceAccount($sourceCode){
        $this->getSelect()->join(
                'omnyfy_easyship_account',
                'main_table.easyship_account_id = omnyfy_easyship_account.entity_id',
                [
                    'name' => 'omnyfy_easyship_account.name',
                    'access_token' => 'omnyfy_easyship_account.access_token',
                    'use_live_rate' => 'omnyfy_easyship_account.use_live_rate',
                    'created_by_mo' => 'omnyfy_easyship_account.created_by_mo',
                ]
            );
        $this->addFieldToFilter('main_table.source_code', $sourceCode);
        if (count($this->getData()) > 0) {
            return $this->getFirstItem();
        } else {
            return null;
        }
    }

    public function getAccountRateOptionBySource($sourceCode) {
        $this->getSelect()->join(
            'omnyfy_easyship_account',
            'main_table.easyship_account_id = omnyfy_easyship_account.entity_id',
            [
                'name' => 'omnyfy_easyship_account.name',
                'access_token' => 'omnyfy_easyship_account.access_token',
                'use_live_rate' => 'omnyfy_easyship_account.use_live_rate',
                'created_by_mo' => 'omnyfy_easyship_account.created_by_mo',
            ]
        )
        ->join(
            'omnyfy_easyship_rate_option',
            'omnyfy_easyship_account.entity_id = omnyfy_easyship_rate_option.easyship_account_id',
            [
                'name_rate_option' => 'omnyfy_easyship_rate_option.name',
                'active_rate_option' => 'omnyfy_easyship_rate_option.is_active',
                'price_rate_option' => 'omnyfy_easyship_rate_option.shipping_rate_option_price',
                'shipping_rate_option_id' => 'omnyfy_easyship_rate_option.shipping_rate_option_id',
            ]
        );

        $this->addFieldToFilter('omnyfy_easyship_rate_option.is_active', true);
        $this->addFieldToFilter('main_table.source_code', $sourceCode);

        if (count($this->getData()) > 0) {
            return $this->getFirstItem();
        } else {
            return null;
        }
    }
}