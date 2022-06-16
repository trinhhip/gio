<?php

namespace OmnyfyCustomzation\CmsBlog\Block\Article\View;

use Exception;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use OmnyfyCustomzation\CmsBlog\Model\Category;
use Omnyfy\Vendor\Api\Data\VendorAttributeInterface;
use Omnyfy\Vendor\Api\VendorAttributeRepositoryInterface;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\Vendor\Api\VendorTypeRepositoryInterface;
use Omnyfy\Vendor\Helper\Media;
use Omnyfy\Vendor\Model\Resource\Vendor\Collection;
use Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory;
use Omnyfy\Vendor\Model\Vendor;
use Omnyfy\VendorSearch\Helper\Data;

class RelatedVendors extends Template
{

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Omnyfy\Vendor\Model\Resource\Location\Collection
     */
    protected $_itemCollection;
    /**
     * @var Data
     */
    protected $_helperData;
    /**
     * @var VendorRepositoryInterface
     */
    protected $_vendorRepository;
    /**
     * @var Media
     */
    protected $_vendorMedia;

    /**
     * @var CollectionFactory
     */
    protected $_vendorCollectionFactory;

    /**
     * @var VendorTypeRepositoryInterface
     */
    protected $_vendorTypeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var VendorAttributeRepositoryInterface
     */
    protected $_vendorMetadataService;

    public function __construct
    (
        Template\Context $context,
        Registry $coreRegistry,
        Data $helperData,
        Media $vendorMedia,
        VendorRepositoryInterface $vendorRepository,
        CollectionFactory $vendorCollectionFactory,
        VendorTypeRepositoryInterface $vendorTypeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        VendorAttributeRepositoryInterface $vendorMetadataService,
        array $data = []
    )
    {
        $this->_coreRegistry = $coreRegistry;
        $this->_helperData = $helperData;
        $this->_vendorMedia = $vendorMedia;
        $this->_vendorRepository = $vendorRepository;
        $this->_vendorCollectionFactory = $vendorCollectionFactory;
        $this->_vendorTypeRepository = $vendorTypeRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_vendorMetadataService = $vendorMetadataService;
        parent::__construct($context, $data);
    }

    /**
     * get realted vendors
     * @return \Omnyfy\Vendor\Model\Resource\Location\Collection
     */
    public function getVendors()
    {
        $article = $this->getArticle();

        $this->_itemCollection = $article->getRelatedServices()
            ->addAttributeToSelect('required_options');


        $this->_itemCollection->setPageSize(
            (int)$this->_scopeConfig->getValue(
                'mfcms/article_view/related_vendors/number_of_vendors',
                ScopeInterface::SCOPE_STORE
            )
        );

        $this->_itemCollection->load();

        return $this->_itemCollection;
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

    public function isSearchByLocation($vendorTypeId)
    {
        try {
            $vendorType = $this->_vendorTypeRepository->getById($vendorTypeId, true);
            return $vendorType->getSearchBy();
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * Retrieve true if Display Related Vendors enabled
     * @return boolean
     */
    public function displayVendors()
    {
        return (bool)$this->_scopeConfig->getValue(
            'mfcms/article_view/related_vendors/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $locationId
     * @return string
     */
    public function getLocationUrl($locationId)
    {
        $url = $this->_helperData->getLocationUrl();
        return $this->getUrl($url, ['id' => $locationId]);
    }

    /**
     * get Image Vendor
     * @param $vendorId
     * @return bool|string
     */
    public function getImage($vendorId)
    {
        try {
            $vendor = $this->_vendorRepository->getById($vendorId);
            if ($vendor) {
                return $this->_vendorMedia->getVendorLogoUrl($vendor);
            }
        } catch (Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }
        return "";
    }

    public function getLocationVendorData($vendorId)
    {
        try {
            /** @var Collection $vendorCollection */
            $vendorCollection = $this->_vendorCollectionFactory->create();
            $vendorCollection->addAttributeToSelect('entity_id', ["eq", $vendorId]);

            if ($vendorCollection->count() == 1) {
                /** @var Vendor $vendor */
                $vendor = $vendorCollection->getFirstItem();
                return $this->getVendorData($vendor);
            }

        } catch (Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }
        return null;
    }

    /**
     * @param Vendor $vendor
     * @return array
     */
    public function getVendorData($vendor)
    {
        try {
            $vendorTypeId = $vendor->getVendorTypeId();
            $vendorType = $this->_vendorTypeRepository->getById($vendorTypeId, true);

            $data = [];

            if (!$vendorType->getSearchBy()) {
                $getVendorAttributeSetId = $vendorType->getVendorAttributeSetId();

                /** @var SearchCriteria $searchCriteria */
                $searchCriteria = $this->_searchCriteriaBuilder->addFilter('attribute_set_id', $getVendorAttributeSetId);
                $attributes = $this->_vendorMetadataService->getList($searchCriteria->create())->getItems();

                /** @var VendorAttributeInterface $attribute */
                foreach ($attributes as $attribute) {

                    if ($attribute->getIsVisibleOnFront()) {
                        $data[$attribute->getAttributeId()]['id'] = $attribute->getAttributeId();
                        $data[$attribute->getAttributeId()]['code'] = $attribute->getAttributeCode();
                        $data[$attribute->getAttributeId()]['label'] = $attribute->getDefaultFrontendLabel();
                        $data[$attribute->getAttributeId()]['type'] = $attribute->getFrontendInput();

                        if ($vendor) {
                            $customerAttribute = $vendor->getData($attribute->getAttributeCode());
                            if ($customerAttribute) {
                                if ($attribute->getFrontendInput() == "text")
                                    $data[$attribute->getAttributeId()]['data'] = $vendor->getResource()->getAttribute($attribute)->getFrontEnd()->getValue($vendor);

                                if ($attribute->getFrontendInput() == "multiselect") {
                                    $data[$attribute->getAttributeId()]['data'] =
                                        explode(",", $vendor->getResource()->getAttribute($attribute)->getFrontEnd()->getValue($vendor));
                                }
                            }
                        }
                    }
                }
            }

            return $data;
        } catch (Exception $exception) {
            $this->_logger->debug($exception->getMessage());
            return [];
        }
    }
}
