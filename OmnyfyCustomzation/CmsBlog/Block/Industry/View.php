<?php
/**
 * Project: CMS M2.
 * User: abhay
 * Date: 3/05/18
 * Time: 11:30 AM
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Industry;

use Magento\Customer\Model\Session;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use OmnyfyCustomzation\CmsBlog\Helper\Data;
use OmnyfyCustomzation\CmsBlog\Model\CategoryFactory;
use OmnyfyCustomzation\CmsBlog\Model\IndustryFactory;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\CollectionFactory;

class View extends Template
{
    protected $coreRegistry;
    protected $industryFactory;
    protected $categoryFactory;
    protected $dataHelper;
    protected $_currencyFactory;
    protected $articleFactory;
    protected $_date;
    /**
     * @var Session
     */
    protected Session $customerSession;
    /**
     * @var RedirectInterface
     */
    protected RedirectInterface $redirect;

    public function __construct(
        Template\Context $context,
        Registry $coreRegistry,
        Session $customerSession,
        CollectionFactory $articlemodelFactory,
        RedirectInterface $redirect,
        Data $dataHelper,
        DateTime $date,
        IndustryFactory $industryFactory,
        CurrencyFactory $_currencyFactory,
        CategoryFactory $categoryFactory,
        array $data = [])
    {
        $this->coreRegistry = $coreRegistry;
        $this->customerSession = $customerSession;
        $this->industryFactory = $industryFactory;
        $this->articleFactory = $articlemodelFactory;
        $this->categoryFactory = $categoryFactory;
        $this->redirect = $redirect;
        $this->_date = $date;
        $this->_currencyFactory = $_currencyFactory;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Return URL for resized CMS Item image
     *
     * @param integer $width
     * @return string|false
     */
    public function getBannerUrl($banner, $width = null, $height = null)
    {
        return $this->dataHelper->imageResize($banner, $width, $height);
    }

    public function getLogoUrl($vendorLogo)
    {
        if (empty($vendorLogo)) {
            return false;
        }
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $vendorLogo;
    }

    /**
     * Return login url for guest users with referer url
     *
     * @return string
     */
    public function getLoginUrl()
    {
        $url = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $login_url = $this->getUrl('customer/account/login', array('referer' => base64_encode($url)));
        return $login_url;
    }

    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCurrentUrl()
    {
        $url = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $url;
    }

    /**
     * Return login url for guest users with referer url
     *
     * @return string
     */
    public function getRedirectedUrl()
    {
        return $this->redirect->getRedirectUrl();
    }

    /* public function getIndustriesByCountry(){
        $collection = $this->industryFactory->create()->getCollection()
                            ->join(
                                array('category' => 'omnyfy_cms_category'),
                                'main_table.by_country = category.category_id',
                                array('title' => 'title','category_snippet' => 'category_snippet')
                            );
        $collection->addFieldToFilter('main_table.by_country',$this->getIndustry()->getByCountry());
        $collection->addFieldToFilter('main_table.id',['neq' => $this->getIndustry()->getId()]);
        $collection->addFieldToFilter('main_table.status','1');

        return $collection;
    } */

    public function isTabVisible($categoryId)
    {
        $articleCollection = $this->isArticle($categoryId);
        if ($articleCollection->getSize() > 0) {
            return true;
        }
        $childIds = $this->getChildCategories($categoryId);
        if ($childIds) {
            foreach ($childIds as $child) {
                $category = $this->getCategory($child);
                if ($category->getIsActive()) {
                    $childArticleCollection = $this->isArticle($child);
                    if ($childArticleCollection->getSize() > 0) {
                        return true;
                        break;
                    }
                }
            }
        }
        return false;
    }

    public function isArticle($categoryId)
    {
        $collection = $this->articleFactory->create()->addFieldToSelect('*')
            ->join(
                array('category_mapping' => 'omnyfy_cms_article_category'),
                'main_table.article_id = category_mapping.article_id',
                array('category_id' => 'category_id')
            );
        $collection->addFieldToFilter('category_id', $categoryId);
        $collection->addFieldToFilter('is_active', '1');
        $collection->addFieldToFilter('publish_time', ['lteq' => $this->_date->gmtDate()]);

        return $collection;
    }

    public function getChildCategories($categoryId)
    {
        return $this->categoryFactory->create()->load($categoryId)->getChildrenIds();
    }

    public function getCategory($categoryId)
    {
        return $this->categoryFactory->create()->load($categoryId);
    }

    public function isChildHeadingVisible($categoryId)
    {
        $childIds = $this->getChildCategories($categoryId);
        if ($childIds) {
            foreach ($childIds as $child) {
                $category = $this->getCategory($child);
                if ($category->getIsActive()) {
                    $childArticleCollection = $this->isArticle($child);
                    if ($childArticleCollection->getSize() > 0) {
                        return true;
                        break;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {

        $this->pageConfig->addBodyClass('cms-industry-view');
        $this->pageConfig->getTitle()->set($this->getIndustry()->getIndustryName());
        return parent::_prepareLayout();
    }

    public function getIndustry()
    {
        return $this->coreRegistry->registry('current_industry');
    }
}
