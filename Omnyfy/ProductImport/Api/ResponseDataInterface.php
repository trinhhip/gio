<?php
namespace Omnyfy\ProductImport\Api;

interface ResponseDataInterface
{
    /**
     * @api
     * @return bool
     */
    public function getSuccess();

    /**
     * @api
     * @param bool $value
     * @return null
     */
    public function setSuccess($value);

    /**
     * @api
     * @return string
     */
    public function getError();

    /**
     * @api
     * @param string $value
     * @return null
     */
    public function setError($value);

    /**
     * @api
     * @return \Omnyfy\ProductImport\Api\ResponseDataProductInterface
     */
    public function getProductData();

    /**
     * @api
     * @param \Omnyfy\ProductImport\Api\ResponseDataProductInterface $value
     * @return null
     */
    public function setProductData($value);
}