<?php
namespace Omnyfy\ProductImport\Api;

interface ResponseDataProductInterface
{
    /**
     * @api
     * @return string
     */
    public function getId();

    /**
     * @api
     * @param string $value
     * @return null
     */
    public function setId($value);

    /**
     * @api
     * @return string
     */
    public function getSku();

    /**
     * @api
     * @param string $value
     * @return null
     */
    public function setSku($value);
}