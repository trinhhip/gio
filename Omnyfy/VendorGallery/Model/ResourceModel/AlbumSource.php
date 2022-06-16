<?php
namespace Omnyfy\VendorGallery\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AlbumSource extends AbstractDb
{
    public function _construct()
    {
        $this->_init('omnyfy_vendor_gallery_album_source', 'entity_id');
    }
}