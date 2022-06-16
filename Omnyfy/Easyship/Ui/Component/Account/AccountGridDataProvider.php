<?php
namespace Omnyfy\Easyship\Ui\Component\Account;

class AccountGridDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $backendSession;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Omnyfy\Easyship\Model\ResourceModel\EasyshipAccount\CollectionFactory $collectionFactory,
        \Magento\Backend\Model\Session $backendSession,
        array $meta = [],
        array $data = []
    ){
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->backendSession = $backendSession;
    }

    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $vendorInfo = $this->backendSession->getVendorInfo();
        $vendorId = isset($vendorInfo['vendor_id'])? $vendorInfo['vendor_id'] : null;

        if(empty($vendorId) ){
            return parent::getData();
        }else {
            $this->getCollection()->getSelect()->where('created_by = ?', $vendorId);
        }

        return [
            'totalRecords' => count($this->getCollection()->getData()),
            'items' =>$this->getCollection()->getData()
        ];
    }
}
