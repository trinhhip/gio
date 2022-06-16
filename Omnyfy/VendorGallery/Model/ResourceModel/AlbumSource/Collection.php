<?php
namespace Omnyfy\VendorGallery\Model\ResourceModel\AlbumLocation;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        $this->_init('Omnyfy\VendorGallery\Model\AlbumSource',
            'Omnyfy\VendorGallery\Model\ResourceModel\AlbumSource');
    }

    public function getAlbumIdBySourceCode($sourceCode)
    {
        $this->addFieldToSelect('album_id')
             ->addFieldToFilter('source_code', $sourceCode)
             ->getData();
        $albumId = [];
        foreach ($this->getData() as $item) {
            $albumId[] = $item['album_id'];
        }
        return $albumId;
    }
}