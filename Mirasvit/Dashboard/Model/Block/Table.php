<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-dashboard
 * @version   1.2.48
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\Model\Block;

use Magento\Framework\DataObject;

class Table extends DataObject
{
    /**
     * @return mixed
     */
    public function getDimensions()
    {
        return $this->getData('dimensions');
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->getData('columns');
    }

    /**
     * @return array|mixed
     */
    public function getSortOrders()
    {
        $val = $this->getData('sort_orders');

        return $val ? $val : [];
    }

    /**
     * @return mixed
     */
    public function getPageSize()
    {
        return $this->getData('page_size');
    }
}