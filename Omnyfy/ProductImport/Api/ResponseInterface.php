<?php
namespace Omnyfy\ProductImport\Api;

interface ResponseInterface
{
    /**
     * @api
     * @return \Omnyfy\ProductImport\Api\ResponseDataInterface[]
     */
    public function getItems();

    /**
     * @api
     * @param \Omnyfy\ProductImport\Api\ResponseDataInterface $value
     * @return null
     */
    public function setItems($value);
}