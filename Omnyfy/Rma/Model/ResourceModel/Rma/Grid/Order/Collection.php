<?php
/**
 * Project: Filter RMA Grid collection based on Vendor Id.
 * Author: seth
 * Date: 10/2/20
 * Time: 2:26 pm
 **/

namespace Omnyfy\Rma\Model\ResourceModel\Rma\Grid\Order;

use Mirasvit\Rma\Api\Data\RmaInterface;
use Magento\Backend\Model\Session as BackendSession;
Use Omnyfy\Vendor\Helper\User;
Use Magento\Backend\Model\Auth\Session as AdminSession;

/**
 * Class Collection
 * @package Omnyfy\Rma\Model\ResourceModel\Rma\Grid
 */
class Collection extends \Mirasvit\Rma\Model\ResourceModel\Rma\Grid\Order\Collection {

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

    protected function _initSelect()
    {
        parent::_initSelect();
        $select = $this->getSelect();
        if ($vendorId = $this->helper->getVendorId()) {
            $rma_ids = $this->helper->getVendorRmaItem($vendorId) ?:  ['NULL'];
            $select->where("main_table.rma_id IN (?)", $rma_ids);
        }
    }
}
