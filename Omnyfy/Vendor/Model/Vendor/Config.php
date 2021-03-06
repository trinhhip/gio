<?php
/**
 * Project: Multi Vendor
 * User: jing
 * Date: 2019-06-05
 * Time: 14:17
 */
namespace Omnyfy\Vendor\Model\Vendor;

class Config extends \Magento\Eav\Model\Config
{
    const XML_PATH_LIST_DEFAULT_SORT_BY = 'omnyfy/frontend/default_sort_by';

    /**
     * @var mixed
     */
    protected $_attributeSetsById;

    /**
     * @var mixed
     */
    protected $_attributeSetsByName;

    /**
     * @var mixed
     */
    protected $_attributeGroupsById;

    /**
     * @var mixed
     */
    protected $_attributeGroupsByName;

    /**
     * @var mixed
     */
    protected $_vendorTypesById;

    /**
     * Array of attributes codes needed for vendor load
     *
     * @var array
     */
    protected $_vendorAttributes;

    /**
     * Vendor Attributes used in  listing
     *
     * @var array
     */
    protected $_usedInListing;

    /**
     * Product Attributes For Sort By
     *
     * @var array
     */
    protected $_usedForSortBy;

    /**
     * @var int|float|string|null
     */
    protected $_storeId = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Set collection factory
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $_setCollectionFactory;

    /**
     * Group collection factory
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $_groupCollectionFactory;

    /**
     * Vendor type factory
     *
     * @var \Omnyfy\Vendor\Model\VendorTypeFactory
     */
    protected $_vendorTypeFactory;

    /**
     * Config factory
     *
     * @var \Omnyfy\Vendor\Model\Resource\Vendor\ConfigFactory
     */
    protected $_configFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Eav\Model\Entity\TypeFactory $entityTypeFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory $entityTypeCollectionFactory,
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Omnyfy\Vendor\Model\Resource\Vendor\ConfigFactory $configFactory
     * @param \Omnyfy\Vendor\Model\VendorTypeFactory $vendorTypeFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Eav\Model\Entity\TypeFactory $entityTypeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory $entityTypeCollectionFactory,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Omnyfy\Vendor\Model\Resource\Vendor\ConfigFactory $configFactory,
        \Omnyfy\Vendor\Model\VendorTypeFactory $vendorTypeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_configFactory = $configFactory;
        $this->_vendorTypeFactory = $vendorTypeFactory;
        $this->_groupCollectionFactory = $groupCollectionFactory;
        $this->_setCollectionFactory = $setCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_eavConfig = $eavConfig;

        parent::__construct($cache, $entityTypeFactory, $entityTypeCollectionFactory, $cacheState, $universalFactory);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Omnyfy\Vendor\Model\Resource\Vendor\Config');
    }

    /**
     * Set store id
     *
     * @param integer $storeId
     * @return \Omnyfy\Vendor\Model\Vendor\Config
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Return store id, if is not set return current app store
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * @return $this
     */
    public function loadAttributeSets()
    {
        if ($this->_attributeSetsById) {
            return $this;
        }

        $attributeSetCollection = $this->_setCollectionFactory->create()->load();

        $this->_attributeSetsById = [];
        $this->_attributeSetsByName = [];
        foreach ($attributeSetCollection as $id => $attributeSet) {
            $entityTypeId = $attributeSet->getEntityTypeId();
            $name = $attributeSet->getAttributeSetName();
            $this->_attributeSetsById[$entityTypeId][$id] = $name;
            $this->_attributeSetsByName[$entityTypeId][strtolower($name)] = $id;
        }
        return $this;
    }

    /**
     * @param string|int|float $entityTypeId
     * @param float|int $id
     * @return false|string
     */
    public function getAttributeSetName($entityTypeId, $id)
    {
        if (!is_numeric($id)) {
            return $id;
        }
        $this->loadAttributeSets();

        if (!is_numeric($entityTypeId)) {
            $entityTypeId = $this->getEntityType($entityTypeId)->getId();
        }
        return isset(
            $this->_attributeSetsById[$entityTypeId][$id]
        ) ? $this->_attributeSetsById[$entityTypeId][$id] : false;
    }

    /**
     * @param string|int|float $entityTypeId
     * @param string|null $name
     * @return false|string|int
     */
    public function getAttributeSetId($entityTypeId, $name = null)
    {
        if (is_numeric($name)) {
            return $name;
        }
        $this->loadAttributeSets();

        if (!is_numeric($entityTypeId)) {
            $entityTypeId = $this->getEntityType($entityTypeId)->getId();
        }
        $name = strtolower($name);
        return isset(
            $this->_attributeSetsByName[$entityTypeId][$name]
        ) ? $this->_attributeSetsByName[$entityTypeId][$name] : false;
    }

    /**
     * @return $this
     */
    public function loadAttributeGroups()
    {
        if ($this->_attributeGroupsById) {
            return $this;
        }

        $attributeSetCollection = $this->_groupCollectionFactory->create()->load();

        $this->_attributeGroupsById = [];
        $this->_attributeGroupsByName = [];
        foreach ($attributeSetCollection as $id => $attributeGroup) {
            $attributeSetId = $attributeGroup->getAttributeSetId();
            $name = $attributeGroup->getAttributeGroupName();
            $this->_attributeGroupsById[$attributeSetId][$id] = $name;
            $this->_attributeGroupsByName[$attributeSetId][strtolower($name)] = $id;
        }
        return $this;
    }

    /**
     * @param float|int|string $attributeSetId
     * @param float|int|string $id
     * @return bool|string
     */
    public function getAttributeGroupName($attributeSetId, $id)
    {
        if (!is_numeric($id)) {
            return $id;
        }

        $this->loadAttributeGroups();

        if (!is_numeric($attributeSetId)) {
            $attributeSetId = $this->getAttributeSetId($attributeSetId);
        }
        return isset(
            $this->_attributeGroupsById[$attributeSetId][$id]
        ) ? $this->_attributeGroupsById[$attributeSetId][$id] : false;
    }

    /**
     * @param float|int|string $attributeSetId
     * @param string $name
     * @return bool|string|int|float
     */
    public function getAttributeGroupId($attributeSetId, $name)
    {
        if (is_numeric($name)) {
            return $name;
        }

        $this->loadAttributeGroups();

        if (!is_numeric($attributeSetId)) {
            $attributeSetId = $this->getAttributeSetId($attributeSetId);
        }
        $name = strtolower($name);
        return isset(
            $this->_attributeGroupsByName[$attributeSetId][$name]
        ) ? $this->_attributeGroupsByName[$attributeSetId][$name] : false;
    }

    /**
     * @return $this
     */
    public function loadVendorTypes()
    {
        if ($this->_vendorTypesById) {
            return $this;
        }

        $vendorTypeCollection = $this->_vendorTypeFactory->create()->getCollection();

        $this->_vendorTypesById = [];

        foreach ($vendorTypeCollection as $id => $type) {
            $name = $type;
            $this->_vendorTypesById[$id] = $name;
            $this->_vendorTypesByName[strtolower($name)] = $id;
        }
        return $this;
    }

    /**
     * @param string $name
     * @return false|string
     */
    public function getVendorTypeId($name)
    {
        if (is_numeric($name)) {
            return $name;
        }

        $this->loadVendorTypes();

        $name = strtolower($name);
        return isset($this->_vendorTypesByName[$name]) ? $this->_vendorTypesByName[$name] : false;
    }

    /**
     * @param float|int|string $id
     * @return false|string
     */
    public function getVendorTypeName($id)
    {
        if (!is_numeric($id)) {
            return $id;
        }

        $this->loadProductTypes();

        return isset($this->_vendorTypesById[$id]) ? $this->_vendorTypesById[$id] : false;
    }

    /**
     * @param \Magento\Framework\DataObject $source
     * @param string $value
     * @return null|mixed
     */
    public function getSourceOptionId($source, $value)
    {
        foreach ($source->getAllOptions() as $option) {
            if (strcasecmp($option['label'], $value) == 0 || $option['value'] == $value) {
                return $option['value'];
            }
        }
        return null;
    }

    /**
     * Load Vendor attributes
     *
     * @return array
     */
    public function getVendorAttributes()
    {
        if (is_null($this->_vendorAttributes)) {
            $this->_vendorAttributes = array_keys($this->getAttributesUsedInListing());
        }
        return $this->_vendorAttributes;
    }

    /**
     * Retrieve resource model
     *
     * @return \Omnyfy\Vendor\Model\Resource\Vendor\Config
     */
    protected function _getResource()
    {
        return $this->_configFactory->create();
    }

    /**
     * Retrieve Attributes used in listing
     *
     * @return array
     */
    public function getAttributesUsedInListing()
    {
        if (is_null($this->_usedInListing)) {
            $this->_usedInListing = [];
            $entityType = \Omnyfy\Vendor\Model\Vendor::ENTITY;
            $attributesData = $this->_getResource()->setStoreId($this->getStoreId())->getAttributesUsedInListing();
            $this->_eavConfig->importAttributesData($entityType, $attributesData);
            foreach ($attributesData as $attributeData) {
                $attributeCode = $attributeData['attribute_code'];
                $this->_usedInListing[$attributeCode] = $this->_eavConfig->getAttribute(
                    $entityType,
                    $attributeCode
                );
            }
        }
        return $this->_usedInListing;
    }

    /**
     * Retrieve Attributes array used for sort by
     *
     * @return array
     */
    public function getAttributesUsedForSortBy()
    {
        if (is_null($this->_usedForSortBy)) {
            $this->_usedForSortBy = [];
            $entityType = \Omnyfy\Vendor\Model\Vendor::ENTITY;
            $attributesData = $this->_getResource()->getAttributesUsedForSortBy();
            $this->_eavConfig->importAttributesData($entityType, $attributesData);
            foreach ($attributesData as $attributeData) {
                $attributeCode = $attributeData['attribute_code'];
                $this->_usedForSortBy[$attributeCode] = $this->_eavConfig->getAttribute($entityType, $attributeCode);
            }
        }
        return $this->_usedForSortBy;
    }

    /**
     * Retrieve Attributes Used for Sort by as array
     * key = code, value = name
     *
     * @return array
     */
    public function getAttributeUsedForSortByArray()
    {
        $options = ['position' => __('Position')];
        foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
            $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        }

        return $options;
    }

    /**
     * Retrieve List Default Sort By
     *
     * @param mixed $store
     * @return string
     */
    public function getListDefaultSortBy($store = null)
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_LIST_DEFAULT_SORT_BY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
}
 