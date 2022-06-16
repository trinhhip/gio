<?php
/**
 * Project: Multi Vendor M2.
 * User: jing
 * Date: 6/6/17
 * Time: 11:00 AM
 */

namespace Omnyfy\RebateCore\Model\ResourceModel\Vendor;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\App\State as AppState;
use Omnyfy\Vendor\Helper\User;

class Collection extends AbstractCollection
{

    protected $_idFieldName = 'entity_id';

    protected $appState;

    protected $backendSession;

    protected $_adminSession;

    protected $_userHelper;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        AdminSession $adminSession,
        User $userHelper,
        AppState $appState,
        $connection = null,
        $resource = null
    )
    {
        $this->_adminSession = $adminSession;
        $this->appState = $appState;
        $this->_userHelper = $userHelper;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    protected function _construct()
    {
        $this->_init('Omnyfy\RebateCore\Model\Vendor', 'Omnyfy\RebateCore\Model\ResourceModel\Vendor');
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $subquery = new \Zend_Db_Expr('(SELECT vendor_id,SUM(rebate_total_amount) as last_rebate_month from omnyfy_rebate_invoice where  entity_id in (select entity_id from omnyfy_rebate_invoice where payment_frequency = 2 AND status = 0) GROUP BY vendor_id)');

        $subquery1 = new \Zend_Db_Expr('(SELECT vendor_id,SUM(rebate_total_amount) as last_annual_rebate from omnyfy_rebate_invoice where  entity_id in (select entity_id from omnyfy_rebate_invoice where payment_frequency = 3 AND status = 0) GROUP BY vendor_id)');
        $this->getSelect()->joinLeft( array( 't' => $subquery ), 'main_table.entity_id = t.vendor_id', array('t.last_rebate_month'))->joinLeft( array( 't1' => $subquery1 ), 'main_table.entity_id = t1.vendor_id', array('t1.last_annual_rebate'));
        return $this;
    }

    protected function _renderFiltersBefore() {
        parent::_renderFiltersBefore();

        if (FrontNameResolver::AREA_CODE != $this->appState->getAreaCode()) {
            return;
        }

        $vendorInfo = $this->getBackendSession()->getVendorInfo();

        $currentUser = $this->_adminSession->getUser();

        if (!$currentUser) {
            return;
        }

        $userVendor = $this->_userHelper->getUserVendor($currentUser->getUserId());

        // If this is set, this will override what is set inside the vendor
        $userStores = $this->_userHelper->getUserStores($currentUser->getUserId());

        if (empty($vendorInfo) && (!$userStores || in_array(0, $userStores))) {
            return;
        }

        if (!empty($vendorInfo) && (!$userStores || in_array(0, $userStores)) && !$vendorInfo['vendor_id']) {
            return;
        }

        if (!empty($vendorInfo)) {
            if (empty($vendorInfo['website_ids'])) {
                $vendorInfo['website_ids'] = [-1];
            }
            if (empty($vendorInfo['store_ids'])) {
                $vendorInfo['store_ids'] = [-1];
            }
            if (empty($vendorInfo['profile_ids'])) {
                $vendorInfo['profile_ids'] = [-1];
            }
            if (empty($vendorInfo['location_ids'])) {
                $vendorInfo['location_ids'] = [-1];
            }
            if (empty($vendorInfo['vendor_id'])) {
                $vendorInfo['vendor_id'] = 0;
            }
        }


        $this->_logger->debug('here2: '. get_class($this));

        if (empty($vendorInfo) && $userStores) {
            $this->filterWebsite($userStores);
        }
        elseif (!empty($vendorInfo)) {
            if ($vendorInfo['vendor_id']) {
                $this->addFieldToFilter('entity_id', $vendorInfo['vendor_id']);
            }
            if ($userStores) {
                $this->filterWebsite($userStores);
            } else {
                $this->filterWebsite($vendorInfo['website_ids']);
            }
            if (!isset($vendorInfo['is_vendor_admin']) || empty($vendorInfo['is_vendor_admin'])) {
                if ($userStores) {
                    $this->filterWebsite($userStores);
                } else {
                    $this->filterWebsite($vendorInfo['website_ids']);
                }
            }
        }

        $this->_logger->debug('filtered vendor collection', $vendorInfo);
    }

    protected function getBackendSession()
    {
        if (null == $this->backendSession) {
            $this->backendSession = \Magento\Framework\App\ObjectManager::getInstance()->get(BackendSession::class);
        }
        return $this->backendSession;
    }

    public function filterWebsite($websiteId)
    {
        $subSql = 'SELECT vendor_id FROM ' . $this->getTable('omnyfy_vendor_profile')
            . ' WHERE website_id IN (?)'
        ;
        $this->addFieldToFilter('entity_id',
            [
                'in' => new \Zend_Db_Expr($this->getConnection()->quoteInto($subSql, $websiteId))
            ]
        );
        return $this;
    }
}
