<?php
namespace Omnyfy\VendorFeatured\Model\SpotlightBannerPlacement;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $dataPersistor;
    protected $loadedData;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $data = $item->getData();
            $data['category_ids'] = explode(',', $data['category_ids']);
            $result = $data;
            $this->loadedData[$item->getBannerId()] = $result;
        }
        $data = $this->dataPersistor->get('omnyfy_vendorfeatured_spotlight_banner');

        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getBannerId()] = $item->getData();
            $this->dataPersistor->clear('omnyfy_vendorfeatured_spotlight_banner');
        }
        return $this->loadedData;
    }
}
