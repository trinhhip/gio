<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-19
 * Time: 14:59
 */
namespace Omnyfy\Approval\Model;

class Product extends \Magento\Framework\Model\AbstractModel implements \Omnyfy\Approval\Api\Data\ProductInterface
{
    protected function _construct()
    {
        $this->_init('Omnyfy\Approval\Model\Resource\Product');
    }

    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    public function getProductName()
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    public function getVendorId()
    {
        return $this->getData(self::VENDOR_ID);
    }

    public function setVendorId($vendorId)
    {
        return $this->setData(self::VENDOR_ID, $vendorId);
    }

    public function getVendorName()
    {
        return $this->getData(self::VENDOR_NAME);
    }

    public function setVendorName($vendorName)
    {
        return $this->setData(self::VENDOR_NAME, $vendorName);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
 