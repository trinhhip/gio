<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Test\Unit\Model\ResourceModel\Entity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Load collection by customer id
     *
     * @param int $id
     * @return $this
     */
    public function filterByCustomerId($id)
    {
        return $this;
    }

    /**
     * Load collection by customer id
     *
     * @return $this
     */
    public function filterByActive()
    {
        return $this;
    }
}
