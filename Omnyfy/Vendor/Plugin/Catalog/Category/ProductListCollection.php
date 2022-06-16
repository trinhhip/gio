<?php


namespace Omnyfy\Vendor\Plugin\Catalog\Category;


use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Omnyfy\Vendor\Model\Vendor;
use Omnyfy\Vendor\Model\VendorFactory;

class ProductListCollection
{
    private $request;
    private $resource;
    private $connection;
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

    public function beforePrepareProductCollection(Layer $subject, Collection $collection): array
    {
        $vendorId = (int)$this->request->getParam('vendor_id');
        if($vendorId  && !$collection->getFlag('filter_by_vendor')
            && $this->helper->isAllProductsEnabled()){
            $select = $this->connection->select()
                ->from($this->connection->getTableName('omnyfy_vendor_vendor_product'),'product_id')
                ->where('vendor_id = ?', $vendorId);
            $productIds = $this->connection->fetchCol($select);
            $vendorModel = $this->vendorFactory->create()->load($vendorId);
            $urlLogo = $this->mediaHlp->getVendorLogoUrl($vendorModel);
            if (!$this->registry->registry('vendor_name')) {
                $this->registry->register('vendor_name', $vendorModel->getName());
            }
            if($urlLogo){
                if (!$this->registry->registry('vendor_logo')) {
                    $this->registry->register('vendor_logo',$urlLogo);
                }
            }
//            $collection->addAttributeToFilter('entity_id', array_unique($productIds));
            $collection->setFlag('filter_by_vendor', true);
        }
        return [$collection];
    }

}
