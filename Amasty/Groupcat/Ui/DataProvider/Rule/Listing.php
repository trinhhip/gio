<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

declare(strict_types=1);

namespace Amasty\Groupcat\Ui\DataProvider\Rule;

use Amasty\Groupcat\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Listing extends AbstractDataProvider
{
    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == 'rule_id') {
            $filter->setField('main_table.' . $filter->getField());
        }
        parent::addFilter($filter);
    }
}
