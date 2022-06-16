<?php

namespace Omnyfy\Vendor\Model\Order\Item;

use Magento\Framework\DataObject;
use Omnyfy\Vendor\Api\Data\OrderItemTaxInterface;

/**
 * Class Tax
 */
final class Tax implements OrderItemTaxInterface
{
    private $id;

    private $title;

    private $itemId;

    private $taxPercent;
    
    private $amount;

    /**
     * Get Tax Id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set Tax Id
     *
     * @return int|null
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get Tax Title
     *
     * @return int|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * set Tax Title
     *
     * @return int|null
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get Item Id
     *
     * @return int|null
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * set Item Id
     *
     * @return int|null
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
        return $this;
    }

    /**
     * Get TaxPercent
     *
     * @return int|null
     */
    public function getTaxPercent()
    {
        return $this->taxPercent;
    }

    /**
     * Set TaxPercent
     *
     * @return int|null
     */
    public function setTaxPercent($taxPercent)
    {
        $this->taxPercent = $taxPercent;
        return $this;
    }

    /**
     * Get Amount
     *
     * @return float|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set Amount
     *
     * @return float|null
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }
}
