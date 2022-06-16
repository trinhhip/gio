<?php
namespace Omnyfy\Vendor\Model\Import;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogImportExport\Model\Import\Product\ImageTypeProcessor;
use Magento\CatalogImportExport\Model\Import\Product\LinkProcessor;
use Magento\CatalogImportExport\Model\Import\Product\MediaGalleryProcessor;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\StatusProcessor;
use Magento\CatalogImportExport\Model\Import\Product\StockProcessor;
use Magento\CatalogImportExport\Model\StockItemImporterInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import as ImportExport;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Store\Model\Store;
use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor;
use Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor;
use Magento\CatalogImportExport\Model\Import\Product\Validator;
use Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor;
use Omnyfy\Vendor\Model\Resource\Vendor as VendorResource;
use Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory as VendorCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory as SourceCollectionFactory;
use Omnyfy\Vendor\Model\Config;
use Omnyfy\Vendor\Model\Resource\Inventory;
use Omnyfy\Vendor\Model\Resource\VendorSourceStock;

class Product extends \Magento\CatalogImportExport\Model\Import\Product
{
    private $_logger;
    private $productEntityLinkField;
    private $productEntityIdentifierField;
    private $multiLineSeparatorForRegexp;
    private $filesystem;
    private $catalogConfig;
    private $stockItemImporter;
    private $imageTypeProcessor;
    private $mediaProcessor;
    private $dateTimeFactory;
    private $productRepository;
    private $statusProcessor;
    private $stockProcessor;
    private $linkProcessor;
    private $fileDriver;
    private const HASH_ALGORITHM = 'sha256';
    protected $vendorResource;
    protected $vendorCollectionFactory;
    protected $productCollectionFactory;
    protected $sourceCollectionFactory;
    protected $inventoryResource;
    protected $vSourceStockResource;
    protected $vendorModelConfig;
    private $serializer;

    private $_permanentVendorAttributes = ['vendor_id', 'source_code'];

    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\ImportExport\Model\Import\Config $importConfig,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory,
        \Magento\CatalogImportExport\Model\Import\Product\OptionFactory $optionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory,
        \Magento\CatalogImportExport\Model\Import\Product\Type\Factory $productTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory,
        \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory $proxyProdFactory,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        DateTime $dateTime,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        StoreResolver $storeResolver,
        SkuProcessor $skuProcessor,
        CategoryProcessor $categoryProcessor,
        Validator $validator,
        ObjectRelationProcessor $objectRelationProcessor,
        TransactionManagerInterface $transactionManager,
        TaxClassProcessor $taxClassProcessor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\Url $productUrl,
        CatalogConfig $catalogConfig = null,
        ImageTypeProcessor $imageTypeProcessor = null,
        MediaGalleryProcessor $mediaProcessor = null,
        StockItemImporterInterface $stockItemImporter = null,
        DateTimeFactory $dateTimeFactory = null,
        ProductRepositoryInterface $productRepository = null,
        StatusProcessor $statusProcessor = null,
        StockProcessor $stockProcessor = null,
        LinkProcessor $linkProcessor = null,
        ?File $fileDriver = null,
        VendorResource $vendorResource,
        VendorCollectionFactory $vendorCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        SourceCollectionFactory $sourceCollectionFactory,
        Config $vendorModelConfig,
        Inventory $inventoryResource,
        VendorSourceStock $vSourceStockResource,
        array $data = [],
        array $dateAttrCodes = []
    ) {
        $this->_eventManager = $eventManager;
        $this->stockRegistry = $stockRegistry;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockStateProvider = $stockStateProvider;
        $this->_catalogData = $catalogData;
        $this->_importConfig = $importConfig;
        $this->_resourceFactory = $resourceFactory;
        $this->_setColFactory = $setColFactory;
        $this->_productTypeFactory = $productTypeFactory;
        $this->_linkFactory = $linkFactory;
        $this->_proxyProdFactory = $proxyProdFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_stockResItemFac = $stockResItemFac;
        $this->_localeDate = $localeDate;
        $this->dateTime = $dateTime;
        $this->indexerRegistry = $indexerRegistry;
        $this->_logger = $logger;
        $this->storeResolver = $storeResolver;
        $this->skuProcessor = $skuProcessor;
        $this->categoryProcessor = $categoryProcessor;
        $this->validator = $validator;
        $this->objectRelationProcessor = $objectRelationProcessor;
        $this->transactionManager = $transactionManager;
        $this->taxClassProcessor = $taxClassProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->productUrl = $productUrl;
        $this->dateAttrCodes = array_merge($this->dateAttrCodes, $dateAttrCodes);
        $this->catalogConfig = $catalogConfig ?: ObjectManager::getInstance()->get(CatalogConfig::class);
        $this->imageTypeProcessor = $imageTypeProcessor ?: ObjectManager::getInstance()->get(ImageTypeProcessor::class);
        $this->mediaProcessor = $mediaProcessor ?: ObjectManager::getInstance()->get(MediaGalleryProcessor::class);
        $this->stockItemImporter = $stockItemImporter ?: ObjectManager::getInstance()
            ->get(StockItemImporterInterface::class);
        $this->statusProcessor = $statusProcessor ?: ObjectManager::getInstance()
            ->get(StatusProcessor::class);
        $this->stockProcessor = $stockProcessor ?: ObjectManager::getInstance()
            ->get(StockProcessor::class);
        $this->linkProcessor = $linkProcessor ?? ObjectManager::getInstance()
                ->get(LinkProcessor::class);
        $this->linkProcessor->addNameToIds($this->_linkNameToId);

        parent::__construct(
            $jsonHelper,
            $importExportData,
            $importData,
            $config,
            $resource,
            $resourceHelper,
            $string,
            $errorAggregator,
            $eventManager,
            $stockRegistry,
            $stockConfiguration,
            $stockStateProvider,
            $catalogData,
            $importConfig,
            $resourceFactory,
            $optionFactory,
            $setColFactory,
            $productTypeFactory,
            $linkFactory,
            $proxyProdFactory,
            $uploaderFactory,
            $filesystem,
            $stockResItemFac,
            $localeDate,
            $dateTime,
            $logger,
            $indexerRegistry,
            $storeResolver,
            $skuProcessor,
            $categoryProcessor,
            $validator,
            $objectRelationProcessor,
            $transactionManager,
            $taxClassProcessor,
            $scopeConfig,
            $productUrl,
            $data,
            $dateAttrCodes,
            $catalogConfig,
            $imageTypeProcessor,
            $mediaProcessor,
            $stockItemImporter,
            $dateTimeFactory,
            $productRepository,
            $statusProcessor,
            $stockProcessor,
            $linkProcessor,
            $fileDriver
        );
        $this->_optionEntity = $data['option_entity'] ??
            $optionFactory->create(['data' => ['product_entity' => $this]]);
        $this->_initAttributeSets()
            ->_initTypeModels()
            ->_initSkus()
            ->initImagesArrayKeys();
        $this->validator->init($this);
        $this->dateTimeFactory = $dateTimeFactory ?? ObjectManager::getInstance()->get(DateTimeFactory::class);
        $this->productRepository = $productRepository ?? ObjectManager::getInstance()
                ->get(ProductRepositoryInterface::class);
        $this->fileDriver = $fileDriver ?: ObjectManager::getInstance()->get(File::class);
        $this->vendorResource = $vendorResource;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->vendorModelConfig = $vendorModelConfig;
        $this->inventoryResource = $inventoryResource;
        $this->vSourceStockResource = $vSourceStockResource;
    }

    protected function _saveProducts()
    {
        $priceIsGlobal = $this->_catalogData->isPriceGlobal();
        $productLimit = null;
        $productsQty = null;
        $entityLinkField = $this->getProductEntityLinkField();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRowsIn = [];
            $entityRowsUp = [];
            $attributes = [];
            $this->websitesCache = [];
            $this->categoriesCache = [];
            $tierPrices = [];
            $mediaGallery = [];
            $labelsForUpdate = [];
            $imagesForChangeVisibility = [];
            $uploadedImages = [];
            $previousType = null;
            $prevAttributeSet = null;

            $importDir = $this->_mediaDirectory->getAbsolutePath($this->getUploader()->getTmpDir());
            $existingImages = $this->getExistingImages($bunch);
            $this->addImageHashes($existingImages);

            foreach ($bunch as $rowNum => $rowData) {
                // reset category processor's failed categories array
                $this->categoryProcessor->clearFailedCategories();

                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $rowScope = $this->getRowScope($rowData);

                $urlKey = $this->getUrlKey($rowData);
                if (!empty($rowData[self::URL_KEY])) {
                    // If url_key column and its value were in the CSV file
                    $rowData[self::URL_KEY] = $urlKey;
                } elseif ($this->isNeedToChangeUrlKey($rowData)) {
                    // If url_key column was empty or even not declared in the CSV file but by the rules it is need to
                    // be setteed. In case when url_key is generating from name column we have to ensure that the bunch
                    // of products will pass for the event with url_key column.
                    $bunch[$rowNum][self::URL_KEY] = $rowData[self::URL_KEY] = $urlKey;
                }

                $rowSku = $rowData[self::COL_SKU];
                $rowSkuNormalized = mb_strtolower($rowSku);

                if (null === $rowSku) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $storeId = !empty($rowData[self::COL_STORE])
                    ? $this->getStoreIdByCode($rowData[self::COL_STORE])
                    : Store::DEFAULT_STORE_ID;
                $rowExistingImages = $existingImages[$storeId][$rowSkuNormalized] ?? [];
                $rowStoreMediaGalleryValues = $rowExistingImages;
                $rowExistingImages += $existingImages[Store::DEFAULT_STORE_ID][$rowSkuNormalized] ?? [];

                if (self::SCOPE_STORE == $rowScope) {
                    // set necessary data from SCOPE_DEFAULT row
                    $rowData[self::COL_TYPE] = $this->skuProcessor->getNewSku($rowSku)['type_id'];
                    $rowData['attribute_set_id'] = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                    $rowData[self::COL_ATTR_SET] = $this->skuProcessor->getNewSku($rowSku)['attr_set_code'];
                }

                // 1. Entity phase
                if ($this->isSkuExist($rowSku)) {
                    // existing row
                    if (isset($rowData['attribute_set_code'])) {
                        $attributeSetId = $this->catalogConfig->getAttributeSetId(
                            $this->getEntityTypeId(),
                            $rowData['attribute_set_code']
                        );

                        // wrong attribute_set_code was received
                        if (!$attributeSetId) {
                            throw new LocalizedException(
                                __(
                                    'Wrong attribute set code "%1", please correct it and try again.',
                                    $rowData['attribute_set_code']
                                )
                            );
                        }
                    } else {
                        $attributeSetId = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                    }

                    $entityRowsUp[] = [
                        'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                        'attribute_set_id' => $attributeSetId,
                        $entityLinkField => $this->getExistingSku($rowSku)[$entityLinkField]
                    ];
                } else {
                    if (!$productLimit || $productsQty < $productLimit) {
                        $entityRowsIn[strtolower($rowSku)] = [
                            'attribute_set_id' => $this->skuProcessor->getNewSku($rowSku)['attr_set_id'],
                            'type_id' => $this->skuProcessor->getNewSku($rowSku)['type_id'],
                            'sku' => $rowSku,
                            'has_options' => isset($rowData['has_options']) ? $rowData['has_options'] : 0,
                            'created_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                            'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                        ];
                        $productsQty++;
                    } else {
                        $rowSku = null;
                        // sign for child rows to be skipped
                        $this->getErrorAggregator()->addRowToSkip($rowNum);
                        continue;
                    }
                }

                if (!array_key_exists($rowSku, $this->websitesCache)) {
                    $this->websitesCache[$rowSku] = [];
                }
                // 2. Product-to-Website phase
                if (!empty($rowData[self::COL_PRODUCT_WEBSITES])) {
                    $websiteCodes = explode($this->getMultipleValueSeparator(), $rowData[self::COL_PRODUCT_WEBSITES]);
                    foreach ($websiteCodes as $websiteCode) {
                        $websiteId = $this->storeResolver->getWebsiteCodeToId($websiteCode);
                        $this->websitesCache[$rowSku][$websiteId] = true;
                    }
                } else {
                    $product = $this->retrieveProductBySku($rowSku);
                    if ($product) {
                        $websiteIds = $product->getWebsiteIds();
                        foreach ($websiteIds as $websiteId) {
                            $this->websitesCache[$rowSku][$websiteId] = true;
                        }
                    }
                }

                // 3. Categories phase
                if (!array_key_exists($rowSku, $this->categoriesCache)) {
                    $this->categoriesCache[$rowSku] = [];
                }
                $rowData['rowNum'] = $rowNum;
                $categoryIds = $this->processRowCategories($rowData);
                foreach ($categoryIds as $id) {
                    $this->categoriesCache[$rowSku][$id] = true;
                }
                unset($rowData['rowNum']);

                // 4.1. Tier prices phase
                if (!empty($rowData['_tier_price_website'])) {
                    $tierPrices[$rowSku][] = [
                        'all_groups' => $rowData['_tier_price_customer_group'] == self::VALUE_ALL,
                        'customer_group_id' => $rowData['_tier_price_customer_group'] ==
                        self::VALUE_ALL ? 0 : $rowData['_tier_price_customer_group'],
                        'qty' => $rowData['_tier_price_qty'],
                        'value' => $rowData['_tier_price_price'],
                        'website_id' => self::VALUE_ALL == $rowData['_tier_price_website'] ||
                        $priceIsGlobal ? 0 : $this->storeResolver->getWebsiteCodeToId($rowData['_tier_price_website']),
                    ];
                }

                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                // 5. Media gallery phase
                list($rowImages, $rowLabels) = $this->getImagesFromRow($rowData);
                $imageHiddenStates = $this->getImagesHiddenStates($rowData);
                foreach (array_keys($imageHiddenStates) as $image) {
                    //Mark image as uploaded if it exists
                    if (array_key_exists($image, $rowExistingImages)) {
                        $uploadedImages[$image] = $image;
                    }
                    //Add image to hide to images list if it does not exist
                    if (empty($rowImages[self::COL_MEDIA_IMAGE])
                        || !in_array($image, $rowImages[self::COL_MEDIA_IMAGE])
                    ) {
                        $rowImages[self::COL_MEDIA_IMAGE][] = $image;
                    }
                }

                $rowData[self::COL_MEDIA_IMAGE] = [];
                list($rowImages, $rowData) = $this->clearNoSelectionImages($rowImages, $rowData);

                /*
                 * Note: to avoid problems with undefined sorting, the value of media gallery items positions
                 * must be unique in scope of one product.
                 */
                $position = 0;
                foreach ($rowImages as $column => $columnImages) {
                    foreach ($columnImages as $columnImageKey => $columnImage) {
                        $uploadedFile = $this->getAlreadyExistedImage($rowExistingImages, $columnImage, $importDir);
                        if (!$uploadedFile && !isset($uploadedImages[$columnImage])) {
                            $uploadedFile = $this->uploadMediaFiles($columnImage);
                            $uploadedFile = $uploadedFile ?: $this->getSystemFile($columnImage);
                            if ($uploadedFile) {
                                $uploadedImages[$columnImage] = $uploadedFile;
                            } else {
                                unset($rowData[$column]);
                                $this->addRowError(
                                    ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE,
                                    $rowNum,
                                    null,
                                    null,
                                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                                );
                            }
                        } elseif (isset($uploadedImages[$columnImage])) {
                            $uploadedFile = $uploadedImages[$columnImage];
                        }

                        if ($uploadedFile && $column !== self::COL_MEDIA_IMAGE) {
                            $rowData[$column] = $uploadedFile;
                        }

                        if (!$uploadedFile || isset($mediaGallery[$storeId][$rowSku][$uploadedFile])) {
                            continue;
                        }

                        $uploadedFileNormalized = ltrim($uploadedFile, '/\\');
                        if (isset($rowExistingImages[$uploadedFileNormalized])) {
                            $currentFileData = $rowExistingImages[$uploadedFileNormalized];
                            $currentFileData['store_id'] = $storeId;
                            $storeMediaGalleryValueExists = isset($rowStoreMediaGalleryValues[$uploadedFileNormalized]);
                            if (array_key_exists($uploadedFile, $imageHiddenStates)
                                && $currentFileData['disabled'] != $imageHiddenStates[$uploadedFile]
                            ) {
                                $imagesForChangeVisibility[] = [
                                    'disabled' => $imageHiddenStates[$uploadedFile],
                                    'imageData' => $currentFileData,
                                    'exists' => $storeMediaGalleryValueExists
                                ];
                                $storeMediaGalleryValueExists = true;
                            }

                            if (isset($rowLabels[$column][$columnImageKey])
                                && $rowLabels[$column][$columnImageKey] !== $currentFileData['label']
                            ) {
                                $labelsForUpdate[] = [
                                    'label' => $rowLabels[$column][$columnImageKey],
                                    'imageData' => $currentFileData,
                                    'exists' => $storeMediaGalleryValueExists
                                ];
                            }
                        } else {
                            if ($column === self::COL_MEDIA_IMAGE) {
                                $rowData[$column][] = $uploadedFile;
                            }
                            $mediaGallery[$storeId][$rowSku][$uploadedFile] = [
                                'attribute_id' => $this->getMediaGalleryAttributeId(),
                                'label' => isset($rowLabels[$column][$columnImageKey])
                                    ? $rowLabels[$column][$columnImageKey]
                                    : '',
                                'position' => ++$position,
                                'disabled' => isset($imageHiddenStates[$columnImage])
                                    ? $imageHiddenStates[$columnImage] : '0',
                                'value' => $uploadedFile,
                            ];
                        }
                    }
                }

                // 6. Attributes phase
                $rowStore = (self::SCOPE_STORE == $rowScope)
                    ? $this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE])
                    : 0;
                $productType = isset($rowData[self::COL_TYPE]) ? $rowData[self::COL_TYPE] : null;
                if ($productType !== null) {
                    $previousType = $productType;
                }
                if (isset($rowData[self::COL_ATTR_SET])) {
                    $prevAttributeSet = $rowData[self::COL_ATTR_SET];
                }
                if (self::SCOPE_NULL == $rowScope) {
                    // for multiselect attributes only
                    if ($prevAttributeSet !== null) {
                        $rowData[self::COL_ATTR_SET] = $prevAttributeSet;
                    }
                    if ($productType === null && $previousType !== null) {
                        $productType = $previousType;
                    }
                    if ($productType === null) {
                        continue;
                    }
                }

                $productTypeModel = $this->_productTypeModels[$productType];
                if (!empty($rowData['tax_class_name'])) {
                    $rowData['tax_class_id'] =
                        $this->taxClassProcessor->upsertTaxClass($rowData['tax_class_name'], $productTypeModel);
                }

                if ($this->getBehavior() == Import::BEHAVIOR_APPEND ||
                    empty($rowData[self::COL_SKU])
                ) {
                    $rowData = $productTypeModel->clearEmptyData($rowData);
                }

                $rowData = $productTypeModel->prepareAttributesWithDefaultValueForSave(
                    $rowData,
                    !$this->isSkuExist($rowSku)
                );
                $product = $this->_proxyProdFactory->create(['data' => $rowData]);

                foreach ($rowData as $attrCode => $attrValue) {
                    $attribute = $this->retrieveAttributeByCode($attrCode);

                    if ('multiselect' != $attribute->getFrontendInput() && self::SCOPE_NULL == $rowScope) {
                        // skip attribute processing for SCOPE_NULL rows
                        continue;
                    }
                    $attrId = $attribute->getId();
                    $backModel = $attribute->getBackendModel();
                    $attrTable = $attribute->getBackend()->getTable();
                    $storeIds = [0];

                    if ('datetime' == $attribute->getBackendType()
                        && (
                            in_array($attribute->getAttributeCode(), $this->dateAttrCodes)
                            || $attribute->getIsUserDefined()
                        )
                    ) {
                        $attrValue = $this->dateTime->formatDate($attrValue, false);
                    } elseif ('datetime' == $attribute->getBackendType() && strtotime($attrValue)) {
                        $attrValue = gmdate(
                            'Y-m-d H:i:s',
                            $this->_localeDate->date($attrValue)->getTimestamp()
                        );
                    } elseif ($backModel) {
                        $attribute->getBackend()->beforeSave($product);
                        $attrValue = $product->getData($attribute->getAttributeCode());
                    }
                    if (self::SCOPE_STORE == $rowScope) {
                        if (self::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
                            // check website defaults already set
                            if (!isset($attributes[$attrTable][$rowSku][$attrId][$rowStore])) {
                                $storeIds = $this->storeResolver->getStoreIdToWebsiteStoreIds($rowStore);
                            }
                        } elseif (self::SCOPE_STORE == $attribute->getIsGlobal()) {
                            $storeIds = [$rowStore];
                        }
                        if (!$this->isSkuExist($rowSku)) {
                            $storeIds[] = 0;
                        }
                    }
                    foreach ($storeIds as $storeId) {
                        if (!isset($attributes[$attrTable][$rowSku][$attrId][$storeId])) {
                            $attributes[$attrTable][$rowSku][$attrId][$storeId] = $attrValue;
                        }
                    }
                    // restore 'backend_model' to avoid 'default' setting
                    $attribute->setBackendModel($backModel);
                }
            }

            // add data assign product to vendor
            $assignDatas = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (isset($rowData['vendor_id']) && isset($rowData['sku'])) {
                    $assignDatas[] = [
                        'vendor_id' => $rowData['vendor_id'],
                        'sku' => $rowData['sku']
                    ];
                }
                if ($this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    unset($bunch[$rowNum]);
                }
            }

            $this->saveProductEntity($entityRowsIn, $entityRowsUp)
                ->_saveProductWebsites($this->websitesCache)
                ->_saveProductCategories($this->categoriesCache)
                ->_saveProductTierPrices($tierPrices)
                ->_saveMediaGallery($mediaGallery)
                ->_saveProductAttributes($attributes)
                ->updateMediaGalleryVisibility($imagesForChangeVisibility)
                ->updateMediaGalleryLabels($labelsForUpdate);

            // Assign Product to Vendor
            foreach ($assignDatas as $data) {
                $productId = $this->productRepository->get($data['sku'])->getId();
                $dataRelation[] = [
                    'vendor_id' => $data['vendor_id'],
                    'product_id' => $productId
                ];
                $this->vendorResource->saveProductRelation($dataRelation);
            }

            $this->assingProductToSource($bunch);

            $this->_eventManager->dispatch(
                'catalog_product_import_bunch_save_after',
                ['adapter' => $this, 'bunch' => $bunch]
            );
        }

        return $this;
    }

    protected function assingProductToSource($bunch) {
        $vendorCollection = $this->vendorCollectionFactory->create();
        $dataAssignSource = [];
        $sourceCollection = $this->sourceCollectionFactory->create();
        $allSourceCodes = $sourceCollection->getAllIds();
        $allVendorIds = $vendorCollection->getAllIds();
        $zendDbExprNull = new \Zend_Db_Expr('NULL');
        foreach ($bunch as $rowNum => $rowData) {
            if (!isset($rowData['product_type']) || $rowData['product_type'] == 'bundle' || $rowData['product_type'] == 'configurable') {
                continue;
            }
            if (!in_array($rowData['vendor_id'], $allVendorIds)) {
                // vendor is not exists
                continue;
            }
            if (!in_array($rowData['source_code'], $allSourceCodes)) {
                // source is not exists
                continue;
            }
            $vendorIdOfSource = $sourceCollection->getItemById($rowData['source_code'])->getVendorId();
            if ($rowData['vendor_id'] != $vendorIdOfSource) {
                // source is not from current vendor
                continue;
            }
            $sourceStockIds = $this->vSourceStockResource->getIdsBySourceCode($rowData['source_code']);
            if (count($sourceStockIds) == 1) {
                // Case Source belong 1 Stock
                $dataAssignSource[$rowData['sku']] = [
                    'source_stock_id' => $sourceStockIds[0],
                    'vendor_id' => $rowData['vendor_id'],
                    'qty' => isset($rowData['qty']) ? $rowData['qty'] : 0,
                    'source_code' => $rowData['source_code']
                ];
            } else {
                // Case Source belong more than 2 Stocks
                $dataAssignSource[$rowData['sku']] = [
                    'source_stock_id' => $sourceStockIds,
                    'vendor_id' => $rowData['vendor_id'],
                    'qty' => isset($rowData['qty']) ? $rowData['qty'] : 0,
                    'source_code' => $rowData['source_code']
                ];
            }
            $vendorCollection->clear();
        }

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addFieldToFilter('sku', ['in' => array_keys($dataAssignSource)]);
        $skuToIds = [];
        foreach($productCollection->getData() as $product) {
            $skuToIds[$product['sku']] = $product['entity_id'];
        }
        $productCollection->clear();

        foreach ($dataAssignSource as $sku => $arr) {
            $vendorId = $arr['vendor_id'];
            $productId = $skuToIds[$sku];
            $sourceModel = $sourceCollection->getItemById($arr['source_code']);
            $productIdsToSource = [
                'sku' => $sku,
                'inventory_id' => $zendDbExprNull,
                'product_id' => $productId,
                'source_code' => $arr['source_code'],
                'quantity' => $arr['qty'],
                'source_stock_id' => $arr['source_stock_id']
            ];

            $assignedSource[0] = [
                'source_code' => $arr['source_code'],
                'name' => $sourceModel->getName(),
                'quantity' => $arr['qty'],
                'source_status' => '1',
                'notify_stock_qty' => '1',
                'notify_stock_qty_use_default' => '1',
                'position' => '1',
                'status' => '1'
            ];

           $this->inventoryResource->importSave($productIdsToSource, $assignedSource);
        }
    }

    protected function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $source->rewind();
        while ($source->valid()) {
            try {
                $rowData = $source->current();
            } catch (\InvalidArgumentException $e) {
                $source->next();
                continue;
            }

            $rowData = $this->_customFieldsMapping($rowData);

            $this->validateRow($rowData, $source->key());

            $source->next();
        }
        $this->checkUrlKeyDuplicates();
        $this->getOptionEntity()->validateAmbiguousData();

        $currentDataSize = 0;
        $bunchRows = [];
        $startNewBunch = false;
        $nextRowBackup = [];
        $maxDataSize = $this->_resourceHelper->getMaxDataSize();
        $bunchSize = $this->_importExportData->getBunchSize();
        $skuSet = [];
        $source->rewind();
        $this->_dataSourceModel->cleanBunches();

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $this->_dataSourceModel->saveBunch($this->getEntityTypeCode(), $this->getBehavior(), $bunchRows);

                $bunchRows = $nextRowBackup;
                $currentDataSize = strlen($this->getSerializer()->serialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
            }
            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                    $allVendorIds = $this->vendorCollectionFactory->create()->getAllIds();
                    $allSourceCodes = $this->sourceCollectionFactory->create()->getAllIds();
                    // Only import product with exists Vendor and Source
                    if (!isset($rowData['vendor_id']) || !isset($rowData['source_code']) || !in_array($rowData['vendor_id'], $allVendorIds) || !in_array($rowData['source_code'], $allSourceCodes)) {
                        $source->next();
                        continue;
                    }
                    if (array_key_exists('sku', $rowData)) {
                        $skuSet[$rowData['sku']] = true;
                    }
                } catch (\InvalidArgumentException $e) {
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                $this->_processedRowsCount++;

                if ($this->validateRow($rowData, $source->key())) {
                    // add row to bunch for save
                    $rowData = $this->_prepareRowForDb($rowData);
                    $rowSize = strlen($this->jsonHelper->jsonEncode($rowData));

                    $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;

                    if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                        $startNewBunch = true;
                        $nextRowBackup = [$source->key() => $rowData];
                    } else {
                        $bunchRows[$source->key()] = $rowData;
                        $currentDataSize += $rowSize;
                    }
                }
                $source->next();
            }
        }
        $this->_processedEntitiesCount = (count($skuSet)) ? : $this->_processedRowsCount;

        return $this;
    }

    protected function _saveStockItem()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $stockData = [];
            $productIdsToReindex = [];
            $stockChangedProductIds = [];
            // Format bunch to stock data rows  
            foreach ($bunch as $rowNum => $rowData) {
                if (!isset($rowData['product_type']) || $rowData['product_type'] == 'bundle' || $rowData['product_type'] == 'configurable') {
                    continue;
                }
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                $row = [];
                $sku = $rowData[self::COL_SKU];
                if ($this->skuProcessor->getNewSku($sku) !== null) {
                    $stockItem = $this->getRowExistingStockItem($rowData);
                    $existingStockItemData = $stockItem->getData();
                    $row = $this->formatStockDataForRow($rowData);
                    $productIdsToReindex[] = $row['product_id'];
                    $storeId = $this->getRowStoreId($rowData);
                    if (!empty(array_diff_assoc($row, $existingStockItemData))
                        || $this->statusProcessor->isStatusChanged($sku, $storeId)
                    ) {
                        $stockChangedProductIds[] = $row['product_id'];
                    }
                }

                if (!isset($stockData[$sku])) {
                    // Add source_code to $stockData
                    $row['source_code'] = $rowData['source_code'];
                    $stockData[$sku] = $row;
                }
            }

            // Insert rows
            if (!empty($stockData)) {
                $this->stockItemImporter->import($stockData);
            }

            $this->reindexStockStatus($stockChangedProductIds);
            $this->reindexProducts($productIdsToReindex);
        }
        return $this;
    }

    private function initImagesArrayKeys()
    {
        $this->_imagesArrayKeys = $this->imageTypeProcessor->getImageTypes();
        return $this;
    }

    private function getOldSkuFieldsForSelect()
    {
        return ['type_id', 'attribute_set_id'];
    }

    private function updateOldSku(array $newProducts)
    {
        $oldSkus = [];
        foreach ($newProducts as $info) {
            $typeId = $info['type_id'];
            $sku = strtolower($info['sku']);
            $oldSkus[$sku] = [
                'type_id' => $typeId,
                'attr_set_id' => $info['attribute_set_id'],
                $this->getProductIdentifierField() => $info[$this->getProductIdentifierField()],
                'supported_type' => isset($this->_productTypeModels[$typeId]),
                $this->getProductEntityLinkField() => $info[$this->getProductEntityLinkField()],
            ];
        }

        $this->_oldSku = array_replace($this->_oldSku, $oldSkus);
    }

    private function getNewSkuFieldsForSelect()
    {
        $fields = ['sku', $this->getProductEntityLinkField()];
        if ($this->getProductEntityLinkField() != $this->getProductIdentifierField()) {
            $fields[] = $this->getProductIdentifierField();
        }
        return $fields;
    }

    private function getSerializer()
    {
        if (null === $this->serializer) {
            $this->serializer = ObjectManager::getInstance()->get(Json::class);
        }
        return $this->serializer;
    }

    private function getFileHash(string $path): string
    {
        return hash_file(self::HASH_ALGORITHM, $path);
    }

    private function getAlreadyExistedImage(array $imageRow, string $columnImage, string $importDir): string
    {
        if (filter_var($columnImage, FILTER_VALIDATE_URL)) {
            $hash = $this->getFileHash($columnImage);
        } else {
            $path = $importDir . DIRECTORY_SEPARATOR . $columnImage;
            $hash = $this->isFileExists($path) ? $this->getFileHash($path) : '';
        }

        return array_reduce(
            $imageRow,
            function ($exists, $file) use ($hash) {
                if (!$exists && isset($file['hash']) && $file['hash'] === $hash) {
                    return $file['value'];
                }

                return $exists;
            },
            ''
        );
    }

    private function addImageHashes(array &$images): void
    {
        $productMediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath(DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product');

        foreach ($images as $storeId => $skus) {
            foreach ($skus as $sku => $files) {
                foreach ($files as $path => $file) {
                    if ($this->fileDriver->isExists($productMediaPath . $file['value'])) {
                        $fileName = $productMediaPath . $file['value'];
                        $images[$storeId][$sku][$path]['hash'] = $this->getFileHash($fileName);
                    }
                }
            }
        }
    }

    private function isFileExists(string $path): bool
    {
        try {
            $fileExists = $this->fileDriver->isExists($path);
        } catch (\Exception $exception) {
            $fileExists = false;
        }

        return $fileExists;
    }

    private function clearNoSelectionImages($rowImages, $rowData)
    {
        foreach ($rowImages as $column => $columnImages) {
            foreach ($columnImages as $key => $image) {
                if ($image === 'no_selection') {
                    unset($rowImages[$column][$key], $rowData[$column]);
                }
            }
        }

        return [$rowImages, $rowData];
    }

    private function getImagesHiddenStates($rowData)
    {
        $statesArray = [];
        $mappingArray = [
            '_media_is_disabled' => '1'
        ];

        foreach ($mappingArray as $key => $value) {
            if (isset($rowData[$key]) && strlen(trim($rowData[$key]))) {
                $items = explode($this->getMultipleValueSeparator(), $rowData[$key]);

                foreach ($items as $item) {
                    $statesArray[$item] = $value;
                }
            }
        }

        return $statesArray;
    }

    private function getImportDir(): string
    {
        $dirConfig = DirectoryList::getDefaultConfig();
        $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];

        return empty($this->_parameters[Import::FIELD_NAME_IMG_FILE_DIR])
            ? $dirAddon . DIRECTORY_SEPARATOR . $this->_mediaDirectory->getRelativePath('import')
            : $this->_parameters[Import::FIELD_NAME_IMG_FILE_DIR];
    }

    private function getSystemFile($fileName)
    {
        $filePath = 'catalog' . DIRECTORY_SEPARATOR . 'product' . DIRECTORY_SEPARATOR . $fileName;
        /** @var \Magento\Framework\Filesystem\Directory\ReadInterface $read */
        $read = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        return $read->isExist($filePath) && $read->isReadable($filePath) ? $fileName : '';
    }

    private function reindexStockStatus(array $productIds): void
    {
        if ($productIds) {
            $this->stockProcessor->reindexList($productIds);
        }
    }

    private function reindexProducts($productIdsToReindex = [])
    {
        $indexer = $this->indexerRegistry->get('catalog_product_category');
        if (is_array($productIdsToReindex) && count($productIdsToReindex) > 0 && !$indexer->isScheduled()) {
            $indexer->reindexList($productIdsToReindex);
        }
    }

    private function isNeedToValidateUrlKey($rowData)
    {
        if (!empty($rowData[self::COL_SKU]) && empty($rowData[self::URL_KEY])
            && $this->getBehavior() === Import::BEHAVIOR_APPEND
            && $this->isSkuExist($rowData[self::COL_SKU])) {
            return false;
        }

        return (!empty($rowData[self::URL_KEY]) || !empty($rowData[self::COL_NAME]))
            && (empty($rowData[self::COL_VISIBILITY])
                || $rowData[self::COL_VISIBILITY]
                !== (string)Visibility::getOptionArray()[Visibility::VISIBILITY_NOT_VISIBLE]);
    }

    private function prepareNewSkuData($sku)
    {
        $data = [];
        foreach ($this->getExistingSku($sku) as $key => $value) {
            $data[$key] = $value;
        }

        $data['attr_set_code'] = $this->_attrSetIdToName[$this->getExistingSku($sku)['attr_set_id']];

        return $data;
    }

    private function _parseAdditionalAttributes($rowData)
    {
        if (empty($rowData['additional_attributes'])) {
            return $rowData;
        }
        $rowData = array_merge($rowData, $this->getAdditionalAttributes($rowData['additional_attributes']));
        return $rowData;
    }

    private function getAdditionalAttributes($additionalAttributes)
    {
        return empty($this->_parameters[Import::FIELDS_ENCLOSURE])
            ? $this->parseAttributesWithoutWrappedValues($additionalAttributes)
            : $this->parseAttributesWithWrappedValues($additionalAttributes);
    }

    private function parseAttributesWithoutWrappedValues($attributesData)
    {
        $attributeNameValuePairs = explode($this->getMultipleValueSeparator(), $attributesData);
        $preparedAttributes = [];
        $code = '';
        foreach ($attributeNameValuePairs as $attributeData) {
            //process case when attribute has ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR inside its value
            if (strpos($attributeData, self::PAIR_NAME_VALUE_SEPARATOR) === false) {
                if (!$code) {
                    continue;
                }
                $preparedAttributes[$code] .= $this->getMultipleValueSeparator() . $attributeData;
                continue;
            }
            list($code, $value) = explode(self::PAIR_NAME_VALUE_SEPARATOR, $attributeData, 2);
            $code = mb_strtolower($code);
            $preparedAttributes[$code] = $value;
        }
        return $preparedAttributes;
    }

    private function parseAttributesWithWrappedValues($attributesData)
    {
        $attributes = [];
        preg_match_all(
            '~((?:[a-zA-Z0-9_])+)="((?:[^"]|""|"' . $this->getMultiLineSeparatorForRegexp() . '")+)"+~',
            $attributesData,
            $matches
        );
        foreach ($matches[1] as $i => $attributeCode) {
            $attribute = $this->retrieveAttributeByCode($attributeCode);
            $value = 'multiselect' != $attribute->getFrontendInput()
                ? str_replace('""', '"', $matches[2][$i])
                : '"' . $matches[2][$i] . '"';
            $attributes[mb_strtolower($attributeCode)] = $value;
        }
        return $attributes;
    }

    public function parseMultiselectValues($values, $delimiter = self::PSEUDO_MULTI_LINE_SEPARATOR)
    {
        if (empty($this->_parameters[Import::FIELDS_ENCLOSURE])) {
            return explode($delimiter, $values);
        }
        if (preg_match_all('~"((?:[^"]|"")*)"~', $values, $matches)) {
            return $values = array_map(
                function ($value) {
                    return str_replace('""', '"', $value);
                },
                $matches[1]
            );
        }
        return [$values];
    }

    private function getMultiLineSeparatorForRegexp()
    {
        if (!$this->multiLineSeparatorForRegexp) {
            $this->multiLineSeparatorForRegexp = in_array(self::PSEUDO_MULTI_LINE_SEPARATOR, str_split('[\^$.|?*+(){}'))
                ? '\\' . self::PSEUDO_MULTI_LINE_SEPARATOR
                : self::PSEUDO_MULTI_LINE_SEPARATOR;
        }
        return $this->multiLineSeparatorForRegexp;
    }

    private function _setStockUseConfigFieldsValues($rowData)
    {
        $useConfigFields = [];
        foreach ($rowData as $key => $value) {
            $useConfigName = $key === StockItemInterface::ENABLE_QTY_INCREMENTS
                ? StockItemInterface::USE_CONFIG_ENABLE_QTY_INC
                : self::INVENTORY_USE_CONFIG_PREFIX . $key;

            if (isset($this->defaultStockData[$key])
                && isset($this->defaultStockData[$useConfigName])
                && !empty($value)
                && empty($rowData[$useConfigName])
            ) {
                $useConfigFields[$useConfigName] = ($value == self::INVENTORY_USE_CONFIG) ? 1 : 0;
            }
        }
        $rowData = array_merge($rowData, $useConfigFields);
        return $rowData;
    }

    private function _customFieldsMapping($rowData)
    {
        foreach ($this->_fieldsMap as $systemFieldName => $fileFieldName) {
            if (array_key_exists($fileFieldName, $rowData)) {
                $rowData[$systemFieldName] = $rowData[$fileFieldName];
            }
        }

        $rowData = $this->_parseAdditionalAttributes($rowData);

        $rowData = $this->_setStockUseConfigFieldsValues($rowData);
        if (array_key_exists('status', $rowData)
            && $rowData['status'] != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        ) {
            if ($rowData['status'] == 'yes') {
                $rowData['status'] = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
            } elseif (!empty($rowData['status']) || $this->getRowScope($rowData) == self::SCOPE_DEFAULT) {
                $rowData['status'] = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
            }
        }
        return $rowData;
    }

    private function isNeedToChangeUrlKey(array $rowData): bool
    {
        $urlKey = $this->getUrlKey($rowData);
        $productExists = $this->isSkuExist($rowData[self::COL_SKU]);
        $markedToEraseUrlKey = isset($rowData[self::URL_KEY]);
        // The product isn't new and the url key index wasn't marked for change.
        if (!$urlKey && $productExists && !$markedToEraseUrlKey) {
            // Seems there is no need to change the url key
            return false;
        }

        return true;
    }

    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }

    private function getProductIdentifierField()
    {
        if (!$this->productEntityIdentifierField) {
            $this->productEntityIdentifierField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getIdentifierField();
        }
        return $this->productEntityIdentifierField;
    }

    private function updateMediaGalleryLabels(array $labels)
    {
        if (!empty($labels)) {
            $this->mediaProcessor->updateMediaGalleryLabels($labels);
        }
    }

    private function updateMediaGalleryVisibility(array $images)
    {
        if (!empty($images)) {
            $this->mediaProcessor->updateMediaGalleryVisibility($images);
        }

        return $this;
    }

    private function parseMultipleValues($labelRow)
    {
        return $this->parseMultiselectValues(
            $labelRow,
            $this->getMultipleValueSeparator()
        );
    }

    private function isSkuExist($sku)
    {
        $sku = strtolower($sku);
        return isset($this->_oldSku[$sku]);
    }

    private function getExistingSku($sku)
    {
        return $this->_oldSku[strtolower($sku)];
    }

    private function formatStockDataForRow(array $rowData): array
    {
        $sku = $rowData[self::COL_SKU];
        $row['product_id'] = $this->skuProcessor->getNewSku($sku)['entity_id'];
        $row['website_id'] = $this->stockConfiguration->getDefaultScopeId();
        $row['stock_id'] = $this->stockRegistry->getStock($row['website_id'])->getStockId();

        $stockItemDo = $this->stockRegistry->getStockItem($row['product_id'], $row['website_id']);
        $existStockData = $stockItemDo->getData();

        if (isset($rowData['qty']) && $rowData['qty'] == 0 && !isset($rowData['is_in_stock'])) {
            $rowData['is_in_stock'] = 0;
        }

        $row = array_merge(
            $this->defaultStockData,
            array_intersect_key($existStockData, $this->defaultStockData),
            array_intersect_key($rowData, $this->defaultStockData),
            $row
        );

        if ($this->stockConfiguration->isQty($this->skuProcessor->getNewSku($sku)['type_id'])) {
            if (isset($rowData['qty']) && $rowData['qty'] == 0) {
                $row['is_in_stock'] = 0;
            }
            $stockItemDo->setData($row);
            $row['is_in_stock'] = $row['is_in_stock'] ?? $this->stockStateProvider->verifyStock($stockItemDo);
            if ($this->stockStateProvider->verifyNotification($stockItemDo)) {
                $date = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));
                $row['low_stock_date'] = $date->format(DateTime::DATETIME_PHP_FORMAT);
            }
            $row['stock_status_changed_auto'] = (int)!$this->stockStateProvider->verifyStock($stockItemDo);
        } else {
            $row['qty'] = 0;
        }

        return $row;
    }

    private function retrieveProductBySku($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $product;
    }

    private function skipRow(
        $rowNum,
        string $errorCode,
        string $errorLevel = ProcessingError::ERROR_LEVEL_NOT_CRITICAL,
        $colName = null
    ): self {
        $this->addRowError($errorCode, $rowNum, $colName, null, $errorLevel);
        $this->getErrorAggregator()
            ->addRowToSkip($rowNum);
        return $this;
    }

    private function getValidationErrorLevel($sku): string
    {
        return (!$this->isSkuExist($sku) && Import::BEHAVIOR_REPLACE !== $this->getBehavior())
            ? ProcessingError::ERROR_LEVEL_CRITICAL
            : ProcessingError::ERROR_LEVEL_NOT_CRITICAL;
    }

    private function getRowStoreId(array $rowData): int
    {
        return !empty($rowData[self::COL_STORE])
            ? (int) $this->getStoreIdByCode($rowData[self::COL_STORE])
            : Store::DEFAULT_STORE_ID;
    }

    private function getRowExistingStockItem(array $rowData): StockItemInterface
    {
        $productId = $this->skuProcessor->getNewSku($rowData[self::COL_SKU])['entity_id'];
        $websiteId = $this->stockConfiguration->getDefaultScopeId();
        return $this->stockRegistry->getStockItem($productId, $websiteId);
    }

    public function validateData()
    {
        if (!$this->_dataValidated) {
            $this->getErrorAggregator()->clear();
            // Custom Message for missing permanent vendor attributes
            $absentVendorColumns = array_diff($this->_permanentVendorAttributes, $this->getSource()->getColNames());
            if(!empty($absentVendorColumns)){
                $this->getErrorAggregator()->addError(
                    'Please check your import to ensure that all columns are included – specifically vendor_id and source_code. Please ensure that you have set the Vendor ID (from Profile) and Source (from Sources) before attempting to re-import'
                );
            }

            // do all permanent columns exist?
            $absentColumns = array_diff($this->_permanentAttributes, $this->getSource()->getColNames());
            $this->addErrors(self::ERROR_CODE_COLUMN_NOT_FOUND, $absentColumns);

            if (ImportExport::BEHAVIOR_DELETE != $this->getBehavior()) {
                // check attribute columns names validity
                $columnNumber = 0;
                $emptyHeaderColumns = [];
                $invalidColumns = [];
                $invalidAttributes = [];
                foreach ($this->getSource()->getColNames() as $columnName) {
                    $columnNumber++;
                    if (!$this->isAttributeParticular($columnName)) {
                        if (trim($columnName) == '') {
                            $emptyHeaderColumns[] = $columnNumber;
                        } elseif (!preg_match('/^[a-z][a-z0-9_]*$/', $columnName)) {
                            $invalidColumns[] = $columnName;
                        } elseif ($this->needColumnCheck && !in_array($columnName, $this->getValidColumnNames())) {
                            $invalidAttributes[] = $columnName;
                        }
                    }
                }
                $this->addErrors(self::ERROR_CODE_INVALID_ATTRIBUTE, $invalidAttributes);
                $this->addErrors(self::ERROR_CODE_COLUMN_EMPTY_HEADER, $emptyHeaderColumns);
                $this->addErrors(self::ERROR_CODE_COLUMN_NAME_INVALID, $invalidColumns);
            }

            if (!$this->getErrorAggregator()->getErrorsCount()) {
                $this->_saveValidatedBunches();
                $this->_dataValidated = true;
            }
        }
        return $this->getErrorAggregator();
    }
}
