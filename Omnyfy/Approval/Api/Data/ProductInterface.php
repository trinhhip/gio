<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-19
 * Time: 15:00
 */
namespace Omnyfy\Approval\Api\Data;

interface ProductInterface
{
    const ID = 'id';

    const PRODUCT_ID = 'product_id';

    const PRODUCT_NAME = 'product_name';

    const VENDOR_ID = 'vendor_id';

    const VENDOR_NAME = 'vendor_name';

    const STATUS = 'status';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @return string|null
     */
    public function getProductName();

    /**
     * @param string $productName
     * @return $this
     */
    public function setProductName($productName);

    /**
     * @return int|null
     */
    public function getVendorId();

    /**
     * @param int $vendorId
     * @return $this
     */
    public function setVendorId($vendorId);

    /**
     * @return string|null
     */
    public function getVendorName();

    /**
     * @param string $vendorName
     * @return $this
     */
    public function setVendorName($vendorName);

    /**
     * @return int|null
     */
    public function getStatus();

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
 