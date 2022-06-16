<?php

namespace Omnyfy\RebateUI\Model\Rebate;

use Omnyfy\RebateCore\Model\ResourceModel\Rebate\CollectionFactory;
use Omnyfy\RebateCore\Api\Data\IRebateRepository;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 * @package Omnyfy\RebateUI\Model\Rebate
 */
class DataProvider extends AbstractDataProvider
{

    /**
     * @var \Prince\Faq\Model\ResourceModel\Faq\CollectionFactory
     */

    public $collection;
    /**
     * @var
     */
    private $loadedData;
    /**
     * @var DataPersistorInterface
     */
    private $rebateRepository;
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blockCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        IRebateRepository $rebateRepository,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->rebateRepository = $rebateRepository;
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
        foreach ($items as $model) {
            $this->loadedData[$model->getEntityId()] = $model->getData();
            $fullData = $this->loadedData;
            $allContribution = $this->rebateRepository->loadContributionByRebate($model->getEntityId());
            $contribution = [];
            foreach ($allContribution as $value) {
                $contribution['rebate_contribution_dynamic_rows'][] = $value;
            }
            if (!empty($contribution['rebate_contribution_dynamic_rows'])) {
                 $this->loadedData[$model->getId()] = array_merge($fullData[$model->getId()], $contribution);
            }            
        }
        $data = $this->dataPersistor->get('rebate');


        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getEntityId()] = $model->getData();
            $this->dataPersistor->clear('rebate');
        }

        return $this->loadedData;
    }
}
