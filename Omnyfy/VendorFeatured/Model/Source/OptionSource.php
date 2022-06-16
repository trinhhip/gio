<?php
namespace Omnyfy\VendorFeatured\Model\Source;

class OptionSource implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $sourceCollectionFactory;

    public function __construct(
        \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory
    ) {
        $this->sourceCollectionFactory = $sourceCollectionFactory;
    }

  /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = [
            'label' => "Select a source",
            'value' => null,
        ];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $labelArray = [];
        $sourceCollection = $this->sourceCollectionFactory->create();
        $sourceCollection->load();

        if($sourceCollection->count() > 0) {
            foreach ($sourceCollection as $source){
                $labelArray[$source->getId()] = $source->getName();
            }
        }
        return $labelArray;
    }
}