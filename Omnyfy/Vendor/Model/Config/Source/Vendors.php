<?php

/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 14/02/2019
 * Time: 5:47 PM
 */

namespace Omnyfy\Vendor\Model\Config\Source;

class Vendors implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_vendorCollectionFactory;
    protected $backendSession;

    public function __construct(
        \Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory $vendorCollectionFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->_vendorCollectionFactory = $vendorCollectionFactory;
        $this->backendSession = $backendSession;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $vendorInfo = $this->backendSession->getVendorInfo();
        $vendorCollection = $this->_vendorCollectionFactory->create();
        if (isset($vendorInfo['vendor_id'])) {
            return $vendorCollection->addFieldToFilter('entity_id', $vendorInfo['vendor_id'])->toOptionArray();
        } else {
            $options[] = [
                'label' => "Please select a Vendor",
                'value' => null,
            ];
            $availableOptions = $this->getVendorsArray();
            foreach ($availableOptions as $option) {
                $options[] = [
                    'value' => $option['value'],
                    'label' => $option['label']
                ];
            }
            return $options;
        }

    }

    /**
     * @return array
     */
    public function getVendorsArray($activeOnly = true)
    {
        /** @var \Omnyfy\Vendor\Model\Resource\Vendor\Collection $vendorCollection */
        $vendorCollection = $this->_vendorCollectionFactory->create();
        if ($activeOnly) {
            $vendorCollection->addFieldToFilter('status', \Omnyfy\Vendor\Model\Source\Status::STATUS_ACTIVE);
        }

        return $vendorCollection->toOptionArray();
    }
}
