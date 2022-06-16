<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Article\View;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Cms\Model\Page;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Module\Manager;
use Magento\Framework\Url\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use OmnyfyCustomzation\CmsBlog\Model\Category;

/**
 * Cms article related products block
 */
class ListProducts extends ListProduct
{
    /**
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * Catalog product visibility
     *
     * @var Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var Manager
     */
    protected $_moduleManager;

    protected $collectionFactory;

    /**
     * Related products block construct
     * @param Context $context
     * @param Visibility $catalogProductVisibility
     * @param Manager $moduleManager
     * @param CollectionFactory $collectionFactory
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Visibility $catalogProductVisibility,
        Manager $moduleManager,
        CollectionFactory $collectionFactory,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        array $data = []
    )
    {
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_moduleManager = $moduleManager;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    /**
     * Retrieve true if Display Related Products enabled
     * @return boolean
     */
    public function displayProducts()
    {
        return (bool)$this->_scopeConfig->getValue(
            'mfcms/article_view/related_products/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return Collection
     */
    public function getItems()
    {
        if (is_null($this->_itemCollection)) {
            $this->_prepareCollection();
        }
        return $this->_itemCollection;
    }

    /**
     * Get Block Identities
     * @return string[]
     */
    public function getIdentities()
    {
        return [Page::CACHE_TAG . '_relatedproducts_' . $this->getArticle()->getId()];
    }

    /**
     * Retrieve articles instance
     *
     * @return Category
     */
    public function getArticle()
    {
        if (!$this->hasData('article')) {
            $this->setData('article',
                $this->_coreRegistry->registry('current_cms_article')
            );
        }
        return $this->getData('article');
    }

    /**
     * @return bool
     */
    public function canDisplay()
    {
        return $this->_getProductCollection()->getSize() ? true : false;
    }

    /**
     * @return Collection|AbstractCollection
     */
    protected function _getProductCollection()
    {
        $article = $this->getArticle();

        $this->_itemCollection = $article->getRelatedProducts()
            ->addAttributeToSelect('required_options');

        if ($this->_moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }

        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $this->_itemCollection->setPageSize(
            (int)$this->_scopeConfig->getValue(
                'mfcms/article_view/related_products/number_of_products',
                ScopeInterface::SCOPE_STORE
            )
        );

        $this->_itemCollection->getSelect()->order('rl.position', 'ASC');

        return $this->_itemCollection;
    }
}
