<?php

namespace Omnyfy\Mcm\Model\ResourceModel;

use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;

class FeesCharges extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;
    /**
     * @var
     */
    protected $mcmConfig;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context, 
        \Magento\Framework\Stdlib\DateTime\DateTime $date, 
        \Magento\Framework\Stdlib\DateTime $dateTime, 
        VendorRepositoryInterface $vendorRepository,
        \Omnyfy\Mcm\Model\Config $mcmConfig,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
        $this->dateTime = $dateTime;
        $this->vendorRepository = $vendorRepository;
        $this->mcmConfig = $mcmConfig;
    }

    /**
     * Define main table
     */
    protected function _construct() {
        $this->_init('omnyfy_mcm_fees_and_charges', 'id');
    }

    /**
     * Process template data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object) {
        $gmtDate = $this->_date->gmtDate();

        if ($object->isObjectNew() && !$object->getCreatedAt()) {
            $object->setCreatedAt($gmtDate);
        }

        $object->setUpdatedAt($gmtDate);
        if ($this->getEnableWholeSale()) {
            $vendorId = $object->getVendorId();
            if ($this->getVendorPayoutBasisType($vendorId) == PayoutBasisType::WHOLESALE_VENDOR_VALUE) {
                $object->setData("seller_fee", 0);
                $object->setData("min_seller_fee", 0);
                $object->setData("max_seller_fee", 0);
                $object->setData("disbursement_fee", 0);
                $object->setData("tax_rate", 0);
            }
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getVendorPayoutBasisType($vendorId){
        if ($vendorId) {
            $vendor = $this->vendorRepository->getById($vendorId);
            return $vendor->getPayoutBasisType();
        }
        return false;
    }

    public function getEnableWholeSale() {
        return $this->mcmConfig->getEnableWholeSale();
    }

    /**
     * Process template data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(
    \Magento\Framework\Model\AbstractModel $object
    ) {
        return parent::_beforeDelete($object);
    }

    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object) {
        return parent::_afterLoad($object);
    }

}
