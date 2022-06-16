<?php
namespace Omnyfy\RebateUI\Block\Adminhtml\Vendor\Edit\Tab;

use Magento\Framework\App\Request\DataPersistorInterface;
use Omnyfy\RebateCore\Api\Data\IRebateRepository;
use Omnyfy\RebateCore\Api\Data\IVendorRebateRepository;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

class VendorRebate extends \Magento\Backend\Block\Widget implements
    \Magento\Backend\Block\Widget\Tab\TabInterface {

    /**
     * Url path
     */
    const URL_REBATE_CHANGE_REQUEST = 'rebate/request/change';

    /**
     * Url path
     */
    const URL_REBATE_CHANGE_REQUEST_ACTION = 'rebate/request/action';
    /**
     * @var RebateCoreRepository
     */
    protected $rebateRepository;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var VendorRebateRepository
     */
    protected $rebateVendorRepository;

    /**
     * @var Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        IRebateRepository $rebateRepository,
        UrlInterface $urlBuilder,
        Registry $registry,
        IVendorRebateRepository $rebateVendorRepository,
        array $data = []
    ) {
        $this->rebateRepository = $rebateRepository;
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
        $this->rebateVendorRepository = $rebateVendorRepository;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Rebate Selection');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Rebate Selection');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    public function isRoleMO()
    {
        $vendorInfo = $this->_backendSession->getVendorInfo();
        return empty($vendorInfo);
    }
    /**
     * @return CurentVendor
     */
    public function getCurentVendor()
    {
        $vendor = $this->registry->registry('current_omnyfy_vendor_vendor');
        if (empty($vendor) || empty($vendor->getId())) {
            return;
        }
        return $vendor;
    }
    /**
     * @return Rebate
     */
    public function getAllRebatesEnable()
    {
        return $this->rebateRepository->getAllRebatesEnable();
    }

    public function getRebateByVendorActive(){
        $vendorId = $this->getCurentVendor()->getId();
        return $this->rebateVendorRepository->getRebateByVendorActive($vendorId);
    }

    public function checkActivedRebateArr(){
        $rebateActive = $this->getRebateByVendorActive();
        $arr = [];
        foreach ($rebateActive as $value) {
            array_push($arr, $value->getRebateId());
        }
        return $arr;
    }

    public function loadContributionByRebate($rebateId){
        return $this->rebateRepository->loadContributionByRebate($rebateId);
    }

    public function getUrlUpdateRebate(){
        return $this->urlBuilder->getUrl($this::URL_REBATE_CHANGE_REQUEST);
    }

    public function loadChangeRequest($vendorRebateId) {
        return $this->rebateVendorRepository->loadChangeRequest($vendorRebateId);
    }

    public function getUrlActionRebate(){
        return $this->urlBuilder->getUrl($this::URL_REBATE_CHANGE_REQUEST_ACTION);
    }
}
