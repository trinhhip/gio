<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Model\Rule\Condition;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Rule\Model\Condition as Condition;
use Magento\Rule\Model\Condition\AbstractCondition;

class Order extends AbstractCondition
{
    /**
     * @var Resource
     */
    private $resource;

    public function __construct(
        Condition\Context $context,
        AppResource $resource,
        array $data = []
    ) {
        $this->resource = $resource;
        parent::__construct($context, $data);
    }

    /**
     * @return $this|AbstractCondition
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'order_num' => __('Number of Completed Orders'),
            'sales_amount' => __('Total Sales Amount'),
            'average_order_value' => __('Average Order Value'),
        ];
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return AbstractCondition
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'numeric';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        $options = [];
        $key = 'value_select_options';

        if (!$this->hasData($key)) {
            $this->setData($key, $options);
        }

        return $this->getData($key);
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $attributeValue = 0;

        if ($customerId = $model->getId()) {
            $db = $this->resource->getConnection('default');
            $select = $db->select()
                ->from(['o' => $this->resource->getTableName('sales_order')], [])
                ->where('o.customer_id = ?', $customerId)
                ->where('o.status = ?', \Magento\Sales\Model\Order::STATE_COMPLETE);

            switch ($this->getAttribute()) {
                case 'order_num':
                    $select->from(null, [new \Zend_Db_Expr('COUNT(*)')]);
                    break;
                case 'sales_amount':
                    $select->from(null, [new \Zend_Db_Expr('SUM(o.base_grand_total)')]);
                    break;
                case 'average_order_value':
                    $select->from(null, [new \Zend_Db_Expr('AVG(o.base_grand_total)')]);
                    break;
            }
            $attributeValue = $db->fetchOne($select);
        }

        return $this->validateAttribute($attributeValue);
    }
}
