<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Article\View;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Cms\Model\Page;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;
use OmnyfyCustomzation\CmsBlog\Model\Category;

/**
 * Cms article related products block
 */
class RelatedProducts extends AbstractProduct implements IdentityInterface
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

    /**
     * Related products block construct
     * @param Context $context
     * @param Visibility $catalogProductVisibility
     * @param Manager $moduleManager
     * @param CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Visibility $catalogProductVisibility,
        Manager $moduleManager,
        CollectionFactory $productCollectionFactory,
        array $data = []
    )
    {
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_moduleManager = $moduleManager;
        parent::__construct($context, $data);
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
     * Premare block data
     * @return $this
     */
    protected function _prepareCollection()
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

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
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
     * Get Block Identities
     * @return string[]
     */
    public function getIdentities()
    {
        return [Page::CACHE_TAG . '_relatedproducts_' . $this->getArticle()->getId()];
    }
}
