<?php
namespace Omnyfy\Easyship\Model\EasyshipAccount;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $_loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Omnyfy\Easyship\Model\ResourceModel\EasyshipAccount\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ){
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if(isset($this->_loadedData)) {
            return $this->_loadedData;
        }

        $items = $this->collection->getItems();

        foreach($items as $account)
        {
            $this->_loadedData[$account->getEntityId()] = $account->getData();
        }

        return $this->_loadedData;
    }
}
