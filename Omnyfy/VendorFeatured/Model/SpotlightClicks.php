<?php
namespace Omnyfy\VendorFeatured\Model;

class SpotlightClicks extends \Magento\Framework\Model\AbstractModel
{
    protected $resourceConnection;

    protected function _construct()
    {
        $this->_init('Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightClicks');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->resourceConnection = $resourceConnection;
    }

    public function deleteClicksByBannerVendorId($bannerVendorId){
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('omnyfy_spotlight_clicks');
        $sql = "DELETE FROM " . $tableName." WHERE banner_vendor_id = ". $bannerVendorId;
        $deletedRows = $connection->query($sql);
        return $deletedRows;
    }
}