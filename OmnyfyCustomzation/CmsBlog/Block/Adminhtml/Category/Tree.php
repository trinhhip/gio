<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile

/**
 * Categories tree block
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Adminhtml\Category;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DB\Helper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use OmnyfyCustomzation\CmsBlog\Model\Category;
use OmnyfyCustomzation\CmsBlog\Model\Config\Source\CategoryTree;

/**
 * Class Tree
 *
 * @package Magento\Catalog\Block\Adminhtml\Category
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Tree extends Template
{

    /**
     * @var string
     */
    protected $_template = 'category/tree.phtml';

    /**
     * @var Session
     */
    protected $_backendSession;

    /**
     * @var Helper
     */
    protected $_resourceHelper;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree
     */
    protected $_categoryTree;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var bool
     */
    protected $_withProductCount;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var UrlInterface
     */
    protected $_backendUrl;
    /**
     * @var Http
     */
    protected $request;
    /**
     * @var Category
     */
    protected $categoryModel;

    public function __construct(
        Context $context,
        Registry $registry,
        ObjectManagerInterface $objectmanager,
        CategoryTree $categoryTree,
        Http $request,
        UrlInterface $backendUrl,
        Category $category,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->_categoryTree = $categoryTree;
        $this->_objectManager = $objectmanager;
        $this->_backendUrl = $backendUrl;
        $this->request = $request;
        $this->categoryModel = $category;
        parent::__construct($context, $data);
    }


    public function getCategory()
    {
        return $this->_objectManager->create(Category::class);
    }

    public function getLoadTreeUrl($expanded = null)
    {
        $params = ['_current' => true, 'id' => null, 'store' => null];

        return $this->getUrl('*/*/categoriesJson', $params);
    }

    public function getTreeJson($parenNodeCategory = null)
    {
        return $this->_jsonEncoder->encode($this->_categoryTree->_getChilds());
    }

    public function getTreeArray($parenNodeCategory = null)
    {
        return $this->_categoryTree->getTree();
    }

    public function getSelectedNode()
    {
        $id = $this->request->getParam('id');
        if ($id) {
            return '#node_' . $id;
        } else if ($parent = $this->request->getParam('parent')) {
            return '#node_' . $parent;
        } else {
            return false;
        }
    }

    public function getAddSubButtonUrl()
    {
        if ($this->request->getParam('id')) {
            return $this->_backendUrl->getUrl('cms/category/new', ['parent' => $this->request->getParam('id')]);
        } else {
            return $this->getAddRootButtonUrl();
        }
    }

    /**
     * @return string
     */
    public function getAddRootButtonUrl()
    {
        return $this->_backendUrl->getUrl('cms/category/new');
    }

    public function getParentId()
    {
        if ($parent = $this->request->getParam('parent')) {
            $category = $this->categoryModel->load($parent);
            $path = $category->getPath();
            if ($path) {
                $parentPath = $path . '/' . $parent;
            } else {
                $parentPath = $parent;
            }
            return $parentPath;
        } else {
            return false;
        }
    }

}
