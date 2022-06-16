<?php
namespace Omnyfy\ProductImport\Cron;

class AssignImage
{
    protected $imageHelper;

    public function __construct(
        \Omnyfy\ProductImport\Helper\ProductImage $imageHelper
    ){
        $this->imageHelper = $imageHelper;
    }

    public function execute(){
        $this->imageHelper->assignImages();
    }
}