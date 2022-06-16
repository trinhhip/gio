<?php
namespace Omnyfy\VendorFeatured\Model\SpotlightBannerVendor;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $dataPersistor;
    protected $loadedData;
    protected $request;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerVendor\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\App\Request\Http $request     * 
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerVendor\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\App\Request\Http $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
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
        $mainBannerId = $this->request->getParam('banner_id');
        foreach ($items as $item) {
            $data = $item->getData();
            $this->loadedData[$item->getBannerId()]['assign_vendors_container'][] = $data;
        }
        $this->loadedData[$mainBannerId]['main_banner_id'] = $mainBannerId;

        return $this->loadedData;
    }
}