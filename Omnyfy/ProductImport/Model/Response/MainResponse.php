<?php
namespace Omnyfy\ProductImport\Model\Response;

class MainResponse implements \Omnyfy\ProductImport\Api\ResponseInterface
{
    private $items;

    public function __construct(){
        $this->items = [];
    }
    /**
     * @api
     * @return \Omnyfy\ProductImport\Api\ResponseDataInterface[]
     */
    public function getItems(){
        return $this->items;
    }

    /**
     * @api
     * @param \Omnyfy\ProductImport\Api\ResponseDataInterface $value
     * @return null
     */
    public function setItems($value){
        $this->items[] = $value;
    }
}