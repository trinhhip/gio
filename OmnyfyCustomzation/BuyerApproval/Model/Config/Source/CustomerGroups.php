<?php


namespace OmnyfyCustomzation\BuyerApproval\Model\Config\Source;


use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class CustomerGroups implements ArrayInterface
{
    protected $_options;
    /**
     * @var CollectionFactory
     */
    public $groupCollectionFactory;

    public function __construct(
        CollectionFactory $groupCollectionFactory
    )
    {
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = $this->groupCollectionFactory->create()->loadData()->toOptionArray();
        }
        unset($this->_options[0]); //remove group not login
        return $this->_options;
    }
}
