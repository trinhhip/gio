<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel\Grid;

use Amasty\Gdpr\Setup\Operation\CreateConsentLogTable;

abstract class AbstractSearchResult extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function joinCustomerData()
    {
        $guest = $this->getConnection()->quote(__('Guest'));

        $this->getSelect()->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'customer.entity_id = main_table.customer_id',
            [
                'email' => $this->getEmailExpr(),
                'name' => new \Zend_Db_Expr(
                    "IF(main_table.customer_id != 0, CONCAT_WS(' ', prefix, "
                    . "firstname, middlename, lastname, suffix), $guest)"
                )
            ]
        );
    }

    protected function getEmailExpr()
    {
        if ($this->getMainTable() === $this->getTable(CreateConsentLogTable::TABLE_NAME)) {
            return new \Zend_Db_Expr('IFNULL(customer.email, main_table.logged_email)');
        }

        return 'customer.email';
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if (is_string($field) && ($field == 'email')) {
            if ($this->getMainTable() === $this->getTable(CreateConsentLogTable::TABLE_NAME)) {
                $field = [
                    'customer.email',
                    'main_table.logged_email'
                ];
                $condition = [
                    $condition,
                    $condition
                ];
            } else {
                $field = 'customer.email';
            }
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
