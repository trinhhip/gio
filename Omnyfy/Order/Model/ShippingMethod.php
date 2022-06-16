<?php
namespace Omnyfy\Order\Model;

use Magento\Framework\Model\AbstractModel;
use Omnyfy\Order\Api\Data\MethodInterface;

class ShippingMethod extends AbstractModel implements \Omnyfy\Order\Api\Data\MethodInterface
{
    public function getMethodCode()
    {
        return $this->getData(self::METHOD_CODE);
    }

    public function getSourceStockId()
    {
        return $this->getData(self::SOURCE_STOCK_ID);
    }

    public function setMethodCode($method)
    {
        return $this->setData(self::METHOD_CODE, $method);
    }

    public function setSourceStockId($sourceStockId)
    {
        return $this->setData(self::SOURCE_STOCK_ID, $sourceStockId);
    }
}
