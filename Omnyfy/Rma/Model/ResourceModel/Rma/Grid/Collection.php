<?php
/**
 * Project: Filter RMA Grid collection based on Vendor Id.
 * Author: seth
 * Date: 10/2/20
 * Time: 2:26 pm
 **/

namespace Omnyfy\Rma\Model\ResourceModel\Rma\Grid;

use Mirasvit\Rma\Api\Data\RmaInterface;
use Magento\Backend\Model\Session as BackendSession;
Use Omnyfy\Vendor\Helper\User;
Use Magento\Backend\Model\Auth\Session as AdminSession;

/**
 * Class Collection
 * @package Omnyfy\Rma\Model\ResourceModel\Rma\Grid
 */
class Collection extends \Mirasvit\Rma\Model\ResourceModel\Rma\Grid\Collection {

    /**
     * @var \Omnyfy\Rma\Helper\Data
     */
    protected $helper;

    protected $backendSession;

    protected $_userHelper;

    protected $_adminSession;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Omnyfy\Rma\Helper\Data $helper
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Omnyfy\Rma\Helper\Data $helper,
        BackendSession $backendSession,
        User $userHelper,
        AdminSession $adminSession,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->helper = $helper;
        $this->backendSession = $backendSession;
        $this->_userHelper = $userHelper;
        $this->_adminSession = $adminSession;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $connection,
            $resource
        );
    }

    /**
     * Added vendor id in filtering the Grid collection.
     */
    protected function initFields()
    {
        $adminId = $this->helper->getAdminUserId();
        /* @noinspection PhpUnusedLocalVariableInspection */
        $select = $this->getSelect();
       
        $vendorInfo = $this->backendSession->getVendorInfo();
        $currentUser = $this->_adminSession->getUser();

        $userVendor = $this->_userHelper->getUserVendor($currentUser->getUserId());

        // If this is set, this will override what is set inside the vendor
        $userStores = $this->_userHelper->getUserStores($currentUser->getUserId());

        if (empty($vendorInfo) && (!$userStores || in_array(0, $userStores))) {
            return;
        }

        if (!empty($vendorInfo) && (!$userStores || in_array(0, $userStores)) && !$vendorInfo['vendor_id']) {
            return;
        }

        // if vendor is empty but a store is set on user, set collection
        if (empty($vendorInfo) && $userStores) {
            $select->where("main_table.store_id IN (". implode(",", $userStores) . ")");
        }

        // if vendor info is not empty
        elseif (!empty($vendorInfo)) {
            // Stores set in user overrides the vendor settings
            if ($userStores) {
                $select->where("main_table.store_id IN (". implode(",", $userStores) . ")");
            } else {
                $select->where("main_table.store_id IN (". implode(",", $vendorInfo['store_ids']) . ")");
            }
        }

        if ($vendorId = $this->helper->getVendorId()) {
            $rma_ids = $this->helper->getVendorRmaItem($vendorId) ?:  ['NULL'];
            $select->where("main_table.rma_id IN (". implode(",", $rma_ids) . ")");
        }else if($adminId){
            $select->where("main_table.user_id = $adminId");
        }

        $select->group('main_table.rma_id')->order('main_table.rma_id DESC');
        $select->columns(['name' => new \Zend_Db_Expr("CONCAT(main_table.firstname, ' ', main_table.lastname)")]);
    }
}
