<?php

namespace Omnyfy\RebateCore\Observer;

use Magento\Framework\Exception\LocalizedException;
use Omnyfy\RebateCore\Api\Data\IVendorRebateRepository;
use Omnyfy\RebateCore\Helper\Data;

/**
 * Class UpdateVendorRebate
 * @package Omnyfy\RebateCore\Observer
 */
class UpdateVendorRebate implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var IVendorRebateRepository
     */
    protected $rebateVendorRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * UpdateVendorRebate constructor.
     * @param IVendorRebateRepository $rebateVendorRepository
     */
    public function __construct(
        Data $helper,
        IVendorRebateRepository $rebateVendorRepository
    )
    {
        $this->rebateVendorRepository = $rebateVendorRepository;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $rebate = $observer->getObject();
            $vendorRebates = $this->rebateVendorRepository->getRebateVendorByIdRebate($rebate->getId());
            foreach ($vendorRebates as $vendorRebate) {
                $vendorRebate->setLockStatus($rebate->getStatus());
                $vendorRebate->save();
            }
        }
    }
}
 