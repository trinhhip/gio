<?php
/**
 * Project: Approval
 * User: jing
 * Date: 5/9/19
 * Time: 10:27 am
 */
namespace Omnyfy\Approval\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ApprovalStatus extends Column
{
    protected $collectionFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Omnyfy\Approval\Model\Resource\Product\CollectionFactory $collectionFactory,
        array $components = [],
        array $data = [])
    {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $name = $this->getData('name');

            $ids = [];
            $exist = false;
            foreach($dataSource['data']['items'] as $item) {
                $ids[] = $item['entity_id'];
                if (isset($item[$name])) {
                    $exist = true;
                }
            }

            if ($exist) {
                return $dataSource;
            }

            //Load status with ids
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('product_id', ['in' => $ids]);
            $result = [];
            foreach($collection as $record) {
                $result[$record['product_id']] = $record['status'];
            }

            foreach($dataSource['data']['items'] as &$item) {
                $item[$name] = isset($result[$item['entity_id']]) ? $result[$item['entity_id']] : null;
            }
        }
        return $dataSource;
    }
}
 