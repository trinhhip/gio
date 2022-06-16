<?php
namespace Omnyfy\VendorGallery\Block\Location;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Omnyfy\VendorGallery\Model\ResourceModel\AlbumLocation\Collection
     */
    private $albumLocationCollection;
    /**
     * @var \Omnyfy\VendorGallery\Model\ResourceModel\Album\Collection
     */
    private $albumCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Omnyfy\VendorGallery\Model\ResourceModel\AlbumLocation\CollectionFactory $albumLocationCollectionFactory
     * @param \Omnyfy\VendorGallery\Model\ResourceModel\Album\CollectionFactory $albumCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Omnyfy\VendorGallery\Model\ResourceModel\AlbumLocation\CollectionFactory $albumLocationCollectionFactory,
        \Omnyfy\VendorGallery\Model\ResourceModel\Album\CollectionFactory $albumCollectionFactory,
        array $data = array()
    ) {
        $this->albumLocationCollection = $albumLocationCollectionFactory->create();
        $this->albumCollection = $albumCollectionFactory->create();
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getLocationId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @return \Omnyfy\VendorGallery\Model\ResourceModel\Album\Collection
     */
    public function getAlbumCollection()
    {
        $locationId = $this->getLocationId();
        $albumLocationCollection = $this->albumLocationCollection;

        $albumId = $albumLocationCollection->getAlbumIdByLocationId($locationId);

        if (count($albumId) > 0) {
            return $this->albumCollection->addFieldToFilter('main_table.entity_id', $albumId)
                ->addFieldToFilter('main_table.status', '1');
        } else {
            // Fix to avoid having to update templates
            // Templatae checks if there is > 0 array size
            return [];
        }
    }
}
