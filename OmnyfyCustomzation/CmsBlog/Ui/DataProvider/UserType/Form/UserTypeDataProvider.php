<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Ui\DataProvider\UserType\Form;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\UserType\Collection;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\UserType\CollectionFactory;
use OmnyfyCustomzation\CmsBlog\Model\UserType;

/**
 * Class DataProvider
 */
class UserTypeDataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $userTypeCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $userTypeCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $userTypeCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
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
        /** @var $userType UserType */
        foreach ($items as $userType) {
            $this->loadedData[$userType->getId()] = $userType->getData();
        }

        $data = $this->dataPersistor->get('current_model');
        if (!empty($data)) {
            $userType = $this->collection->getNewEmptyItem();
            $userType->setData($data);
            $this->loadedData[$userType->getId()] = $userType->getData();
            $this->dataPersistor->clear('current_model');
        }

        return $this->loadedData;
    }
}
