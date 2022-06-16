<?php

namespace Omnyfy\Vendor\Helper;

class Location extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_customerSession;
    protected $_locationFactory;
    private $providerCollection;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Omnyfy\Vendor\Model\LocationFactory $locationFactory
    )
    {
        parent::__construct($context);
        $this->_locationFactory = $locationFactory;
    }

    public function getVendorIdByLocationId($locationId)
    {
        $collection = $this->_locationFactory->create()->getCollection();
        $collection->addFieldToSelect('vendor_id');
        $collection->addFieldToFilter('entity_id', $locationId);

        if ($collection->getSize() > 0) {
            return $collection->getFirstItem()->getVendorId();
        }
        return '';
    }

    public function getProvider($locationId){
        if($this->isBookingModuleOutputEnabled() && !$this->providerCollection && $locationId) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $providerCollection = $objectManager->create(\Omnyfy\Booking\Model\Resource\Provider\Collection::class);
            $this->providerCollection = $providerCollection
                ->addStatusFilter(1)->filterLocationId($locationId);
        }
        return $this->providerCollection;

    }

    public function isBookingModuleOutputEnabled(): bool
    {
        return $this->isModuleOutputEnabled('Omnyfy_Booking');

    }

}
