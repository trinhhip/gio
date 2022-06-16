<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Learn;

use Magento\Cms\Model\Page;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;
use OmnyfyCustomzation\CmsBlog\Helper\Data;
use OmnyfyCustomzation\CmsBlog\Model\CategoryFactory;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\Collection;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\CollectionFactory;

class Popular extends Template
{
    /**
     * Cms collection
     *
     * @var \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category\Collection
     */
    protected $_categoryCollection = null;

    /**
     * Cms collection
     *
     * @var Collection
     */
    protected $_articleCollection = null;

    /**
     * Cms factory
     *
     * @var CategoryFactory
     */
    protected $_categorymodelFactory;
    /**
     * Cms factory
     *
     * @var CategoryFactory
     */
    protected $_articlemodelFactory;

    /** @var Data */
    protected $_dataHelper;

    /**
     * @var DateTime
     */
    protected $_date;

    protected $_filesystem;
    protected $_imageFactory;
    protected $_cmsPage;

    public function __construct(
        Template\Context $context,
        \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category\CollectionFactory $categorymodelFactory,
        CollectionFactory $articlemodelFactory,
        AdapterFactory $imageFactory,
        DateTime $date,
        Data $dataHelper,
        Page $cmsPage,
        array $data = []
    )
    {

        parent::__construct($context, $data);
        $this->_cmsPage = $cmsPage;
        $this->_categorymodelFactory = $categorymodelFactory;
        $this->_articlemodelFactory = $articlemodelFactory;
        $this->_imageFactory = $imageFactory;
        $this->_filesystem = $context->getFilesystem();
        $this->_date = $date;
        $this->_dataHelper = $dataHelper;
        $this->_isScopePrivate = true;
    }

    protected function _prepareLayout()
    {
        $collection = $this->getCollection();

        parent::_prepareLayout();

        return $this;
    }

    public function getCollection()
    {
        $this->_articleCollection = $this->_getCollection()->addFieldToSelect('*');
        $this->_articleCollection->setOrder('article_counter', 'desc');
        $this->_articleCollection->addFieldToFilter('is_active', '1');
        $this->_articleCollection->addFieldToFilter('article_counter', ['neq' => '0']);
        $this->_articleCollection->addFieldToFilter('publish_time', ['lteq' => $this->_date->gmtDate()]);
        $this->_articleCollection->setPageSize(8);

        return $this->_articleCollection;
    }

    public function _getCollection()
    {
        return $this->_articlemodelFactory->create();
    }
}
