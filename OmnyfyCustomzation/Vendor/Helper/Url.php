<?php


namespace OmnyfyCustomzation\Vendor\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\UrlInterface;
use Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Omnyfy\Vendor\Model\VendorFactory;

class Url extends AbstractHelper
{
    const VENDOR_ACTIVE = 1;
    /**
     * @var CollectionFactory
     */
    public $vendorCollection;
    /**
     * @var ProductCollectionFactory
     */
    public $productCollection;
    /**
     * @var CategoryCollectionFactory
     */
    public $categoryCollection;
    /**
     * @var ResourceConnection
     */
    public $resource;
    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    public $_urlBuilder;
    /**
     * @var VendorFactory
     */
    public $vendorFactory;

    /**
     * Url constructor.
     * @param Context $context
     * @param CollectionFactory $vendorCollection
     * @param ProductCollectionFactory $productCollection
     * @param CategoryCollectionFactory $categoryCollection
     * @param ResourceConnection $resource
     * @param VendorFactory $vendorFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $vendorCollection,
        ProductCollectionFactory $productCollection,
        CategoryCollectionFactory $categoryCollection,
        ResourceConnection $resource,
        VendorFactory $vendorFactory

    )
    {
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->vendorCollection = $vendorCollection;
        $this->productCollection = $productCollection;
        $this->categoryCollection = $categoryCollection;
        $this->resource = $resource;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context);
    }

    public function isDuplicateUrl($url, $vendorId)
    {
        if ($this->isHaveVendorUrl($url, $vendorId)) {
            return true;
        }
        if ($this->isHaveProductUrl($url)) {
            return true;
        }
        if ($this->isHaveCategoryUrl($url)) {
            return true;
        }
        return false;
    }

    public function generateUrl($name)
    {
        return strtolower(str_replace(' ', '-', $name));
    }

    public function newUrl($urlKey)
    {
        return $urlKey . mt_rand(0, 100);
    }

    public function isHaveVendorUrl($url, $vendorId)
    {
        return $this->vendorCollection->create()
            ->addAttributeToFilter('url_key', $url)
            ->addAttributeToFilter('entity_id', ['neq' => $vendorId])
            ->getSize();
    }

    public function isHaveProductUrl($url)
    {
        return $this->productCollection->create()->addAttributeToFilter('url_key', $url)->getSize();
    }

    public function isHaveCategoryUrl($url)
    {
        return $this->categoryCollection->create()->addAttributeToFilter('url_key', $url)->getSize();
    }

    public function getVendorIdByUrl($url)
    {
        $connection = $this->resource->getConnection();
        $vendorTable = $connection->getTableName('omnyfy_vendor_vendor_entity');
        $attrTable = $connection->getTableName('omnyfy_vendor_vendor_entity_varchar');
        $sql = $connection->select()->from(
            ['vd' => $vendorTable],
            [
                'vendor_id' => 'vd.entity_id',
            ]
        )->join(
            ['at' => $attrTable],
            'vd.entity_id = at.entity_id'
        )
            ->where('at.value = ?', $url)
            ->where('vd.status = ?', self::VENDOR_ACTIVE);
        return $connection->fetchOne($sql);
    }

    public function getVendorUrl($vendor)
    {
        $vendorUrl = $vendor->getUrlKey();
        if (!$vendor->getUrlKey()) {
            $vendorModel = $this->vendorFactory->create()->load($vendor->getId());
            $vendorUrl = $vendorModel->getUrlKey();
        }
        return $this->_urlBuilder->getUrl('shop/' . $vendorUrl . '.html');
    }
}
