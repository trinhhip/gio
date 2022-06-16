<?php
/**
 * Project: CMS M2.
 * User: abhay
 * Date: 3/05/18
 * Time: 11:30 AM
 */

namespace OmnyfyCustomzation\CmsBlog\Block;

use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\ToolTemplate\CollectionFactory;

class ToolTemplate extends Template
{
    protected $coreRegistry;
    protected $_toolTemplateModelFactory;
    protected $_date;

    public function __construct(
        Template\Context $context,
        Registry $coreRegistry,
        CollectionFactory $toolTemplateModelFactory,
        array $data = [])
    {
        $this->coreRegistry = $coreRegistry;
        $this->_toolTemplateModelFactory = $toolTemplateModelFactory;
        parent::__construct($context, $data);
    }

    public function getToolTemplate()
    {
        return $this->coreRegistry->registry('current_tooltemplate');
    }

    public function getLogoUrl($vendorLogo)
    {
        if (empty($vendorLogo)) {
            return false;
        }
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $vendorLogo;
    }

    public function isToolTemplate()
    {
        $collection = $this->_toolTemplateModelFactory->create()->addFieldToSelect('*');
        $collection->setOrder('position', 'asc');
        $collection->setOrder('id', 'desc');
        $collection->addFieldToFilter('status', '1');

        if ($collection->getSize() > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {

        $this->pageConfig->addBodyClass('cms-tooltemplate-view');
        $this->pageConfig->getTitle()->set('Tools and Templates');
        return parent::_prepareLayout();
    }
}
