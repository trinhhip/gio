<?php
namespace Omnyfy\ProductImport\Model\Response;

class ResponseDataProduct implements \Omnyfy\ProductImport\Api\ResponseDataProductInterface
{
    private $id;
    private $sku;

    public function __construct(){
        $this->id = "";
        $this->sku = "";
    }

    /**
     * @api
     * @return string
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @api
     * @param string $value
     * @return null
     */
    public function setId($value){
        $this->id = $value;
    }

    /**
     * @api
     * @return string
     */
    public function getSku(){
        return $this->sku;
    }

    /**
     * @api
     * @param string $value
     * @return null
     */
    public function setSku($value){
        $this->sku = $value;
    }
}