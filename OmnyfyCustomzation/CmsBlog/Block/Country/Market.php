<?php
/**
 * Project: CMS M2.
 * User: abhay
 * Date: 26/4/18
 * Time: 03:00 PM
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Country;

use Magento\Customer\Model\Session;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use OmnyfyCustomzation\CmsBlog\Helper\Data;
use OmnyfyCustomzation\CmsBlog\Model\CategoryFactory;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Industry\CollectionFactory;

class Market extends Template
{
    protected $coreRegistry;
    protected $countryCollectionFactory;
    protected $industryCollectionFactory;
    protected $categoryFactory;
    protected $dataHelper;
    protected $_currencyFactory;
    /**
     * @var Session
     */
    protected Session $customerSession;

    public function __construct(
        Template\Context $context,
        Registry $coreRegistry,
        Session $customerSession,
        Data $dataHelper,
        \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        CollectionFactory $industryCollectionFactory,
        CurrencyFactory $_currencyFactory,
        CategoryFactory $categoryFactory,
        array $data = [])
    {
        $this->coreRegistry = $coreRegistry;
        $this->customerSession = $customerSession;
        $this->industryCollectionFactory = $industryCollectionFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
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
    public function getResizedImage($banner, $width = null, $height = null)
    {
        return $this->dataHelper->imageResize($banner, $width, $height);
    }

    public function getIndustryCollection()
    {
        return $this->industryCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('status', '1')
            ->setOrder('industry_name', 'ASC');
    }

    public function getLogoUrl($logo)
    {
        if (empty($logo)) {
            return false;
        }
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $logo;
    }

    /**
     * Return login url for guest users with referer url
     *
     * @return string
     */
    public function getLoginUrl()
    {
        $url = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $this->getUrl('customer/account/login', array('referer' => base64_encode($url)));
    }

    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCategory($categoryId)
    {
        return $this->categoryFactory->create()->load($categoryId);
    }

    /* public function getChildCategories(){
        return $this->categoryFactory->create()->load($this->getCountry()->getIndustryInfoCategory());
    } */

    public function getCurrentUrl()
    {
        $url = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $url;
    }

    public function getCountryCollection()
    {
        return $this->countryCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('status', '1')
            ->setOrder('country_name', 'ASC');
    }

    public function getCountryUrl($countryId)
    {
        return $this->getUrl('cms/country/view/id', array('id' => $countryId));
    }

    public function getIndustryCategories($industryId)
    {
        $industryCategory = $this->categoryFactory->create()->load($industryId)->getChildrenIds();
        return array_slice($industryCategory, 0, 5, true);
    }

    public function getMapPosition($value, $position)
    {
        $finalVal = $value / $position * 100;
        return number_format((float)$finalVal, 2, '.', '') . '%';
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {

        $this->pageConfig->addBodyClass('cms-country-market');
        $this->pageConfig->getTitle()->set('Export markets finder');
        #$this->pageConfig->setKeywords($category->getMetaKeywords());
        #$this->pageConfig->setDescription($category->getMetaDescription());
        /* $this->pageConfig->addRemotePageAsset(
            $category->getCategoryUrl(),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        ); */

        return parent::_prepareLayout();
    }
}
