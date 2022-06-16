<?php
namespace Omnyfy\VendorFeatured\Model\VendorSpotlight;

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
     * @param \Magento\Framework\App\Request\Http $request
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
        $col = $this->collection->getSelect()
            ->joinLeft(
                ['click' => 'omnyfy_spotlight_clicks'],
                'main_table.banner_vendor_id = click.banner_vendor_id',
                [
                    'clicks_per_banner' => new \Zend_Db_expr('COUNT(click.banner_vendor_id)'),
                ]
            )
            ->group('main_table.banner_vendor_id');

        $items = $this->collection->getItems();
        $mainVendorId = $this->request->getParam('vendor_id');
        if (count($items)) {
            foreach ($items as $item) {
                $data = $item->getData();
                $mainVendorId = $data['vendor_id'];
                $this->loadedData[$item->getVendorId()]['vendor_spotlight_container'][] = $data;
            }
        }
        $this->loadedData[$mainVendorId]['main_vendor_id'] = $mainVendorId;
        return $this->loadedData;
    }
}
