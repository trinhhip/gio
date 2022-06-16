<?php
namespace Omnyfy\Order\Api\Data;

interface MethodInterface
{
    const SOURCE_STOCK_ID = 'source_stock_id';

    const METHOD_CODE = 'method_code';

    /**
     * @return string|null
     */
    public function getMethodCode();

    /**
     * @return int
     */
    public function getSourceStockId();

    /**
     * @param string $method
     * @return $this
     */
    public function setMethodCode($method);

    /**
     *
     * @param int
     * @return $this
     */
    public function setSourceStockId($sourceStockId);
}
