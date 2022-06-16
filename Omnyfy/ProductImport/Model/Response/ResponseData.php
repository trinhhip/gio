<?php
namespace Omnyfy\ProductImport\Model\Response;

class ResponseData implements \Omnyfy\ProductImport\Api\ResponseDataInterface
{
    private $success;
    private $error;
    private $productData;

    public function __construct(){
        $this->success = false;
        $this->error = "";
        $this->productData = [];
    }

    /**
     * @api
     * @return bool
     */
    public function getSuccess(){
        return $this->success;
    }

    /**
     * @api
     * @param bool $value
     * @return null
     */
    public function setSuccess($value){
        $this->success = $value;
    }

    /**
     * @api
     * @return string
     */
    public function getError(){
        return $this->error;
    }

    /**
     * @api
     * @param string $value
     * @return null
     */
    public function setError($value){
        $this->error = $value;
    }

    /**
     * @api
     * @return \Omnyfy\ProductImport\Api\ResponseDataProductInterface
     */
    public function getProductData(){
        return $this->productData;
    }

    /**
     * @api
     * @param \Omnyfy\ProductImport\Api\ResponseDataProductInterface $value
     * @return null
     */
    public function setProductData($value){
        $this->productData = $value;
    }
}