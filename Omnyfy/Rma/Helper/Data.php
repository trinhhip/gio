<?php
/**
 * Project:
 * Author: seth
 * Date: 21/2/20
 * Time: 1:59 pm
 **/

namespace Omnyfy\Rma\Helper;


use Magento\User\Model\UserFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var UserFactory
     */
    protected $userFactory;

    protected $session;

    protected $authSession;
    /**
     * Data constructor.
     * @param UserFactory $userFactory
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->userFactory = $userFactory;
        $this->context = $context;
        $this->session = $session;
        $this->connection = $connection;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    /**
     * @param $userId
     * @return \Magento\User\Model\User|null
     */
    public function getUser($userId)
    {
        $user = $this->userFactory->create()->load($userId);

        if (!$user->getId()) {
            return null;
        }

        return $user;
    }

    /**
     * Get current login details.
     *
     * @return bool|array
     */
    public function getVendorId() {
        $vendorInfo = $this->session->getVendorInfo();
        if (!empty($vendorInfo)) {
            if (isset($vendorInfo['vendor_id'])) {
                return $vendorInfo['vendor_id'];
            }
        }
        return false;
    }

    public function getVendorRmaItem($vendorId)
    {
        $rmaIdArray = array();
        $connection = $this->connection->getConnection();
        $sql = "SELECT rma_id FROM mst_rma_item WHERE vendor_id = $vendorId";
		$result = $connection->fetchAll($sql);
        foreach ($result as $data) {
            array_push($rmaIdArray,$data['rma_id']);
        }

        $sql2 = "SELECT rma_id from mst_rma_item inner join sales_order_item WHERE mst_rma_item.order_item_id = sales_order_item.item_id AND sales_order_item.vendor_id = $vendorId AND  mst_rma_item.qty_requested > 0 and mst_rma_item.vendor_id = 0";
        $result2 = $connection->fetchAll($sql2);
        foreach ($result2 as $data) {
            array_push($rmaIdArray,$data['rma_id']);
        }
        return $rmaIdArray;
    }

    public function getAdminUserId()
    {
        $user = $this->authSession->getUser();
        if($user){
            $roleData = $user->getRole()->getData();
            if ($roleData['role_name'] != 'Administrators') {
                return $user->getId();
            }
        }
    }
}
