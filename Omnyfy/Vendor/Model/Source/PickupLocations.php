<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 14/02/2019
 * Time: 5:47 PM
 */

namespace Omnyfy\Vendor\Model\Source;

class PickupLocations implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $vSourceStockCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock\CollectionFactory $vSourceStockCollectionFactory
    )
    {
        $this->vSourceStockCollectionFactory = $vSourceStockCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getLocationsArray();
    }

    /**
     * @return array
     */
    public function getLocationsArray() {
        $vSourceStockCollection = $this->vSourceStockCollectionFactory->create();
        $vSourceStockCollection->joinSourceStockLink();
        $vSourceStockCollection->setOrder('priority', 'ASC');
        $vSourceStockCollection->load();
        $options = [];
        $sourceCodes = [];
        foreach ($vSourceStockCollection as $vSourceStock) {
            $sourceCode = $vSourceStock->getSourceCode();
            if (!in_array($sourceCode, $sourceCodes)) {
                $options[] = ["value" => $vSourceStock->getId(), "label" => $vSourceStock->getName()];
            }
            $sourceCodes[] = $sourceCode;
        }

        return $options;
    }
}