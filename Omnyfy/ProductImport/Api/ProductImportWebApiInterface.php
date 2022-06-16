<?php
namespace Omnyfy\ProductImport\Api;

interface ProductImportWebApiInterface
{
    /**
     * Imports products from json
     *
     * @api
     * @return \Omnyfy\ProductImport\Api\ResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function add();

    /**
     * Update products from json
     *
     * @api
     * @return \Omnyfy\ProductImport\Api\ResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function update();
}