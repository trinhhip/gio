<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Category;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use OmnyfyCustomzation\CmsBlog\Model\Category;
use OmnyfyCustomzation\CmsBlog\Model\Url;

/**
 * Cms category info
 */
class Info extends Template
{
    /**
     * @var FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var Url
     */
    protected $_url;

    /**
     * Construct
     *
     * @param Context $context
     * @param Registry $coreRegistry ,
     * @param FilterProvider $filterProvider
     * @param Url $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FilterProvider $filterProvider,
        Url $url,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_filterProvider = $filterProvider;
        $this->_url = $url;
    }

    /**
     * Retrieve article content
     *
     * @return string
     */
    public function getContent()
    {
        $category = $this->getCategory();
        $key = 'filtered_content';
        if (!$category->hasData($key)) {
            $cotent = $this->_filterProvider->getPageFilter()->filter(
                $category->getContent()
            );
            $category->setData($key, $cotent);
        }
        return $category->getData($key);
    }

    /**
     * Retrieve category instance
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('current_cms_category');
    }

}
