<?php

namespace Omnyfy\VendorSearch\Block\Search;

use Omnyfy\Vendor\Api\Data\VendorTypeInterface;

class Form extends \Magento\Framework\View\Element\Template
{
    /** @var \Omnyfy\Vendor\Api\VendorAttributeRepositoryInterface $_vendorMetadataService */
    protected $_vendorMetadataService;

    /** @var \Magento\Framework\Api\SearchCriteriaBuilder $_searchCriteriaBuilder */
    protected $_searchCriteriaBuilder;

    /** @var \Omnyfy\VendorSearch\Helper\Data $_data */
    protected $_helperData;
    /**
     * @var \Omnyfy\Vendor\Api\VendorTypeRepositoryInterface
     */
    private $vendorTypeRepository;
    /**
     * @var \Omnyfy\Vendor\Model\Resource\VendorType\CollectionFactory
     */
    private $vendorTypeCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Omnyfy\Vendor\Api\VendorAttributeRepositoryInterface $vendorMetadataService,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Omnyfy\Vendor\Api\VendorTypeRepositoryInterface $vendorTypeRepository,
        \Omnyfy\Vendor\Model\Resource\VendorType\CollectionFactory $vendorTypeCollectionFactory,
        \Omnyfy\VendorSearch\Helper\Data $helperData,
        array $data = []
    ){
        $this->_vendorMetadataService = $vendorMetadataService;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_helperData = $helperData;
        parent::__construct($context, $data);
        $this->vendorTypeRepository = $vendorTypeRepository;
        $this->vendorTypeCollectionFactory = $vendorTypeCollectionFactory;
    }

    public function getNumActiveForms(){
        return $this->getData('num_active_forms');
    }

    public function getSearchForms(){
        $forms = $this->vendorTypeCollectionFactory->create()->addFieldToFilter(VendorTypeInterface::STATUS, 1)->setOrder('type_name','ASC')->getItems();

        $formsArray = [];

        foreach ($forms as $form) {
            $formsArray[] = [
                'name' => (string) $form->getData('type_name'),
                'default_distance' => '5',
                'default_sort_order' => 'name',
                'vendor_type_id' => (string) $form->getData('type_id'),
                'action_url' => 'vendorsearch/result/*',
                'search_fields' => []
                ];
        }

        return $formsArray;
    }

    public function isFormActive($vendorType, $currentFormId, $isFirstForm){

        if ($vendorType == "" && $isFirstForm)
            return "active";


        if ($vendorType == $currentFormId)
            return "active";

        return "";
    }

    public function isOptionActive($currentValue, $optionValue){
        if ($currentValue == $optionValue)
            return "selected";
        return "";
    }

    public function getTypesDropDown(){
        return $this->getChildHtml('vendor.search.form.types.container');
    }

    public function getFieldOptions($field){

        try {
            $attributeValues = [];

            if (key_exists('attribute_code', $field)) {
                /** @var \Omnyfy\Vendor\Api\Data\VendorAttributeInterface $attribute */
                $attribute = $this->_vendorMetadataService->get($field['attribute_code']);

                $options = $attribute->getOptions();
                $attributeValues = [];

                foreach ($options as $option) {
                    $attributeValues[$option->getValue()] = $option->getLabel();
                }
            }

            return $attributeValues;

        } catch (\Exception $exception){
            $this->_logger->debug($exception->getMessage());
            return [];
        }
    }

    public function getSearchPostUrl($uri,$param = null)
    {
        return $this->getUrl($uri, $param);
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return mixed
     */
    public function isDisplayForm(){
        return $this->_helperData->isSearchForm();
    }

    /**
     * @return mixed
     */
    public function isDistance(){
        return $this->_helperData->isFilters();
    }

    /**
     * @return mixed
     */
    public function isEnabled(){
        return $this->_helperData->isEnabled();
    }
}
