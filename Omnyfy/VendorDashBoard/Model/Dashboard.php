<?php
namespace Omnyfy\VendorDashBoard\Model;

use Magento\Authorization\Model\ResourceModel\Role\Grid\CollectionFactory;

class Dashboard
{
    /**
     * @var CollectionFactory
     */
    public $collectionFactory;

    /**
     * Dashboard constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray(): array
    {
        $roles = $this->getRoles();
        $data = [];
        $data[]  = ['value' => 0,'label' => "None"];
        foreach ($roles as $key => $label) {
            $data[]  = ['value' => $key,'label' => $label];
        }

        return $data;
    }

    public function getRoles()
    {

        $collection = $this->collectionFactory->create();

        $role_group = [];
        foreach ($collection as $items) {
            $role_group[$items->getData("role_id")] = $items->getData("role_name");
        }

        return $role_group;
    }
}
