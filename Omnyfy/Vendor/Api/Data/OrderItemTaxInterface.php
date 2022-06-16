<?php
namespace Omnyfy\Vendor\Api\Data;

interface OrderItemTaxInterface
{
    /**
     * Get Tax Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * set Tax Id
     *
     * @return int|null
     */
    public function setId($id);

    /**
     * Get Tax Title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * set Tax Title
     *
     * @return string|null
     */
    public function setTitle($title);

    /**
     * Get Item Id
     *
     * @return int|null
     */
    public function getItemId();

    /**
     * set Item Id
     *
     * @return int|null
     */
    public function setItemId($itemId);

    /**
     * Get Tax Percent
     *
     * @return int|null
     */
    public function getTaxPercent();

    /**
     * set Tax Percent
     *
     * @return int|null
     */
    public function setTaxPercent($taxPercent);

    /**
     * Get Tax Amount
     *
     * @return float|null
     */
    public function getAmount();

    /**
     * set Amount
     *
     * @return float|null
     */
    public function setAmount($amount);
}
