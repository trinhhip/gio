<?php
namespace Omnyfy\ProductImport\Cron;

class DownloadImage
{
    protected $imageHelper;

    public function __construct(
        \Omnyfy\ProductImport\Helper\ProductImage $imageHelper
    ){
        $this->imageHelper = $imageHelper;
    }

    public function execute(){
        $this->imageHelper->downloadImages();
    }
}