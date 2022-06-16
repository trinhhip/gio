<?php
namespace Omnyfy\VendorDashBoard\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

class Dashboard extends \Magento\Framework\View\Element\Template
{
    const DASHBOARD_ROLE = "Omnyfy_VendorDashBoard/dashboard_general/dashboard_role";
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_adminSession;
    /**
     * @var \Magento\Authorization\Model\ResourceModel\Role\Grid\CollectionFactory
     */
    protected $_roleCollectionFactory;

    /**
     * Dashboard constructor.
     * @param \Magento\Backend\Model\Auth\Session $adminSession
     * @param \Magento\Authorization\Model\ResourceModel\Role\Grid\CollectionFactory $roleCollectionFactory
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magento\Authorization\Model\ResourceModel\Role\Grid\CollectionFactory $roleCollectionFactory,
        Template\Context $context,
       array $data = []
    )
    {
        $this->_adminSession = $adminSession;
        $this->_roleCollectionFactory = $roleCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     * is user admin
     */
    public function isUserAdmin()
    {
        $flag = false;
        $configRole = $this->getConfigUserRole();
        $roleName = $this->getRoleNameById($configRole);
        $roleData = $this->_adminSession->getUser()->getRole()->getData();
        if($roleData && $roleName){
            if(in_array($roleData['role_name'],$roleName)){
                $flag = true;
            }
        }
        return $flag;
    }

    /**
     * @return mixed
     */
    public function getConfigUserRole(){
        return $this->_scopeConfig->getValue(self::DASHBOARD_ROLE);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getRoleNameById($id){
        $name = array();
        $roles = $this->_roleCollectionFactory->create()
            ->addFieldToFilter("role_id",array('in' => explode(",",$id)));
        if($roles->getSize() > 0){
            foreach ($roles as $role){
                $name[] = $role->getRoleName();
            }
        }
        return $name;
    }
}
