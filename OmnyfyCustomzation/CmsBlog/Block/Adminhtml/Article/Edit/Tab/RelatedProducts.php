<?php

namespace OmnyfyCustomzation\CmsBlog\Block\Adminhtml\Article\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\WebsiteFactory;

/**
 * Admin cms article edit form related products tab
 */
class RelatedProducts extends Extended implements TabInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Status;
     */
    protected $_status;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var Visibility
     */
    protected $_visibility;

    /**
     * @var WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param Status $status
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $visibility
     * @param WebsiteFactory $websiteFactory
     * @param Registry $coreRegistry
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Status $status,
        CollectionFactory $productCollectionFactory,
        Visibility $visibility,
        WebsiteFactory $websiteFactory,
        Registry $coreRegistry,
        array $data = []
    )
    {
        $this->_status = $status;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_visibility = $visibility;
        $this->_websiteFactory = $websiteFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData(
            'grid_url'
        ) ? $this->getData(
            'grid_url'
        ) : $this->getUrl(
            'cms/article/relatedProductsGrid',
            ['_current' => true]
        );
    }

    public function getTabLabel()
    {
        return __('Related Products1');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Related Products11');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Set grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('related_products_section');
        $this->setDefaultSort('article_id');
        $this->setUseAjax(true);
        if ($this->getArticle() && $this->getArticle()->getId()) {
            $this->setDefaultFilter(['in_products' => 1]);
        }
        if ($this->isReadonly()) {
            $this->setFilterVisibility(false);
        }
    }

    /**
     * Retrieve currently edited article model
     *
     * @return array|null
     */
    public function getArticle()
    {
        return $this->_coreRegistry->registry('current_model');
    }

    /**
     * Checks when this block is readonly
     *
     * @return bool
     */
    public function isReadonly()
    {
        //return $this->getArticle() && $this->getArticle()->getRelatedReadonly();
        return false;
    }

    /**
     * Add filter
     *
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in article flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Retrieve selected related products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getProductsRelated();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedRelatedProducts());
        }
        return $products;
    }

    /**
     * Retrieve related products
     *
     * @return array
     */
    public function getSelectedRelatedProducts()
    {
        $products = [];
        foreach ($this->_coreRegistry->registry('current_model')->getRelatedProducts() as $product) {
            $products[$product->getId()] = ['position' => $product->getPosition()];
        }
        return $products;
    }

    /**
     * Prepare collection
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $article = $this->getArticle();
        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addWebsiteNamesToResult();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        if (!$this->isReadonly()) {
            $this->addColumn(
                'in_products',
                [
                    'type' => 'checkbox',
                    'name' => 'in_products',
                    'values' => $this->_getSelectedProducts(),
                    'align' => 'center',
                    'index' => 'entity_id',
                    'header_css_class' => 'col-select',
                    'column_css_class' => 'col-select'
                ]
            );
        }

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );


        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku'
            ]
        );

        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->_visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'websites',
                [
                    'header' => __('Websites'),
                    'sortable' => false,
                    'index' => 'websites',
                    'type' => 'options',
                    'options' => $this->_websiteFactory->create()->getCollection()->toOptionHash(),
                    'header_css_class' => 'col-websites',
                    'column_css_class' => 'col-websites',
                    'filter_condition_callback' => array($this, '_filterWebsiteConditionCallback')
                ]
            );
        }

        $this->addColumn(
            'status',
            [
                'header' => __('Status1'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_status->getOptionArray(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status',
                'frame_callback' => array(
                    $this->getLayout()->createBlock('OmnyfyCustomzation\CmsBlog\Block\Adminhtml\Grid\Column\Statuses'),
                    'decorateStatus'
                ),
            ]
        );

        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'name' => 'position',
                'type' => 'number',
                'validate_class' => 'validate-number',
                'index' => 'position',
                'editable' => true,
                'edit_only' => false,
                'sortable' => false,
                'filter' => false,
                'header_css_class' => 'col-position',
                'column_css_class' => 'col-position'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Add website filter
     * @param Collection $collection
     * @param  $column
     * @return $this
     */
    protected function _filterWebsiteConditionCallback($collection, $column)
    {
        if ($column->getFilter()->getValue()) {
            $this->getCollection()->addWebsiteFilter();
        }

        return $this;
    }
}
