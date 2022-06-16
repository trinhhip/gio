<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Ui\DataProvider\Form;

use Amasty\GroupAssign\Model\ResourceModel\Rule\Collection;
use Amasty\GroupAssign\Model\Rule;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;

class RuleDataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    public function __construct(
        Collection $collection,
        DataPersistorInterface $dataPersistor,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();

        /** @var Rule $rule */
        foreach ($items as $rule) {
            $this->loadedData[$rule->getId()] = $rule->getData();
        }
        $data = $this->dataPersistor->get('amasty_groupassign_rule');

        if (!empty($data)) {
            $rule = $this->collection->getNewEmptyItem();
            $rule->setData($data);
            $this->loadedData[(int)$rule->getId()] = $rule->getData();
            $this->dataPersistor->clear('amasty_groupassign_rule');
        }

        return $this->loadedData;
    }
}
