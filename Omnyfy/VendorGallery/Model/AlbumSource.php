<?php
namespace Omnyfy\VendorGallery\Model;

use \Magento\Framework\Model\AbstractModel;

class AlbumSource extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Omnyfy\VendorGallery\Model\ResourceModel\AlbumSource');
    }
}