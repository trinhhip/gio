<?php


namespace Omnyfy\Vendor\Plugin\SearchAdapter;


use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Omnyfy\Vendor\Model\Vendor;
use Omnyfy\Vendor\Model\VendorFactory;

class Mapper
{
    private $request;
    private $resource;
    private $connection;
    private $_vendorProductIds = [];
    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    private $helper;
    /**
     * @var VendorFactory
     */
    private $vendorFactory;
    /**
     * @var \Omnyfy\Vendor\Helper\Media
     */
    private $mediaHlp;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * ProductListCollection constructor.
     * @param RequestInterface $request
     * @param ResourceConnection $resource
     * @param \Amasty\Shopby\Helper\Data $helper
     * @param VendorFactory $vendorFactory
     * @param \Omnyfy\Vendor\Helper\Media $mediaHlp
     * @param Registry $registry
     */
    public function __construct(
        RequestInterface $request,
        ResourceConnection $resource,
        \Amasty\Shopby\Helper\Data $helper,
        VendorFactory $vendorFactory,
        \Omnyfy\Vendor\Helper\Media $mediaHlp,
        Registry $registry
    )
    {
        $this->request = $request;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->helper = $helper;
        $this->vendorFactory = $vendorFactory;
        $this->mediaHlp = $mediaHlp;
        $this->registry = $registry;
    }

    public function afterBuildQuery(
        \Magento\Elasticsearch7\SearchAdapter\Mapper $subject,
        $query): array
    {
        $vendorId = (int)$this->request->getParam('vendor_id');
        if(!$vendorId && $this->request->getModuleName() == 'shop'){
            $vendorId = (int)$this->request->getParam('id');
        }
        if($vendorId && empty($this->_vendorProductIds)
            && $this->helper->isAllProductsEnabled()){
            $select = $this->connection->select()
                ->from($this->connection->getTableName('omnyfy_vendor_vendor_product'),'product_id')
                ->where('vendor_id = ?', $vendorId);
            $productIds = $this->connection->fetchCol($select);
            $this->_vendorProductIds = empty($productIds) ? [NULL] : $productIds;
        }
        if($this->_vendorProductIds){
            $query['body']['query']['bool']['filter'] = ['ids' => [ 'values' => $this->_vendorProductIds]];
        }
        return $query;
    }
}
