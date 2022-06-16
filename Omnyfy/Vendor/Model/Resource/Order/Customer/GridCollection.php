<?php
namespace Omnyfy\Vendor\Model\Resource\Order\Customer;

use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Backend\Model\Session as BackendSession;
use Omnyfy\Vendor\Helper\User;
use Magento\Backend\Model\Auth\Session as AdminSession;

class GridCollection extends \Magento\Sales\Model\ResourceModel\Order\Customer\Collection
{
    /**
     * @var BackendSession
     */
    protected $backendSession;
    /**
     * @var User
     */
    protected $_userHelper;
    /**
     * @var AdminSession
     */
    protected $_adminSession;

    /**
     * GridCollection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\DataObject\Copy\Config $fieldsetConfig
     * @param BackendSession $backendSession
     * @param User $userHelper
     * @param AdminSession $adminSession
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param string $modelName
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig,
        BackendSession $backendSession,
        User $userHelper,
        AdminSession $adminSession,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $modelName = self::CUSTOMER_MODEL_NAME
    )
    {
        $this->backendSession = $backendSession;
        $this->_userHelper = $userHelper;
        $this->_adminSession = $adminSession;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $eavConfig, $resource, $eavEntityFactory, $resourceHelper, $universalFactory, $entitySnapshot, $fieldsetConfig, $connection, $modelName);
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $vendorInfo = $this->backendSession->getVendorInfo();

        $currentUser = $this->_adminSession->getUser();
        // If this is set, this will override what is set inside the vendor
        $userStores = $this->_userHelper->getUserStores($currentUser->getUserId());

        if (empty($vendorInfo) && (!$userStores || in_array(0, $userStores))) {
            $this->_eventManager->dispatch('omnyfy_vendor_order_customer_grid_render_filter_before', ['collection' => $this]);
            return;
        }

        if (!empty($vendorInfo) && (!$userStores || in_array(0, $userStores)) && !$vendorInfo['vendor_id']) {
            $this->_eventManager->dispatch('omnyfy_vendor_order_customer_grid_render_filter_before', ['collection' => $this]);
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
                //$vendorInfo['vendor_id'] = 1;
            }
        }

        if (empty($vendorInfo)) {
            if ($userStores) {
                $this->addFieldToFilter('website_id', ['in' => $userStores]);
            }
        }

        if (!empty($vendorInfo)) {
            if ($userStores) {
                $this->addFieldToFilter('website_id', ['in' => $userStores]);
            } else {
                $this->addFieldToFilter('website_id', ['in' => $vendorInfo['website_ids']]);
            }
        }

        if (!empty($vendorInfo)) {
            if ($vendorInfo['vendor_id']) {
                $cvTable = 'omnyfy_vendor_vendor_customer';
                $this->addFieldToFilter(
                    'entity_id',
                    [
                        'in' => new \Zend_Db_Expr(
                            'SELECT customer_id FROM ' . $cvTable . ' WHERE vendor_id=' . $vendorInfo['vendor_id']
                        )
                    ]
                );
            }
        }

        $this->_logger->debug('filtered create new order for customer collection', $vendorInfo);

        $this->_eventManager->dispatch('omnyfy_vendor_order_customer_grid_render_filter_before', ['collection' => $this]);

        return $this;
    }
}
