<?php
namespace Omnyfy\ProductImport\Helper;
use BigBridge\ProductImport\Api\ImportConfig;
use BigBridge\ProductImport\Api\Data\Product;
use BigBridge\ProductImport\Api\Data\SimpleProduct;
use BigBridge\ProductImport\Api\Data\ConfigurableProduct;

class ProductImage extends \Magento\Framework\App\Helper\AbstractHelper
{
    const LIMIT_PRODUCT_IMAGES = 800;

    protected $directoryList;
    protected $fileDriver;
    protected $file;
    protected $productRepository;
    protected $importerFactory;
    protected $importHelper;
    protected $imageFactory;
    protected $imageCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \BigBridge\ProductImport\Api\ImporterFactory $importerFactory,
        \Omnyfy\ProductImport\Helper\ProductImport $importHelper,
        \Omnyfy\ProductImport\Model\ProductImageImportFactory $imageFactory,
        \Omnyfy\ProductImport\Model\ResourceModel\ProductImageImport\CollectionFactory $imageCollectionFactory
    ){
        $this->directoryList = $directoryList;
        $this->fileDriver = $fileDriver;
        $this->file = $file;
        $this->productRepository = $productRepository;
        $this->importerFactory = $importerFactory;
        $this->importHelper = $importHelper;
        $this->imageFactory = $imageFactory;
        $this->imageCollectionFactory = $imageCollectionFactory;
        parent::__construct($context);
    }

    public function downloadImages(){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/omnyfy_productimport_download.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $images = $this->getImagesToDownload();

        if ($images->count() > 0) {

            $totalImageCount = $images->count();
            $totalFailedImageCount = 0;

            $logger->info("Image download started, images count: ". $totalImageCount);

            foreach ($images as $image) {
                $failed = $image->getFailedAttempts();

                try {
                    # set status processsing
                    # download image from url and store on local drive.
                    $image->setDownloadStatus('processing');
                    list($image, $failed) = $this->downloadExternalImages($image, $logger, $failed);

                } catch (\Exception $e) {
                    # increase 1 in the "failed_attempts", until failed_attempts equals 5.
                    $failed += 1;
                    $totalFailedImageCount += 1;
                    $logger->info("Failed: could not download ". $image->getImageUrl(). ". Error: ". $e->getMessage());
                }
                
                # set status pending failed_attempts less than 5, othewise change download status to "error".
                if ($image->getFailedAttempts() < $failed) {
                    $image->setFailedAttempts($failed);

                    if ($failed >= 5) {
                        $image->setDownloadStatus('error');
                    }else{
                        $image->setDownloadStatus('pending');
                    }
                }
                $image->save();
            }

            $logger->info("Image download completed, downloaded images count: ". ($totalImageCount - $totalFailedImageCount). " failed images count ". $totalFailedImageCount);

        }
    }

    public function assignImages(){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/omnyfy_productimport_assign.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $imageCollection = $this->getImagesToAssign();
        $log = "";

        if (count($imageCollection) > 0) {
            $config = new ImportConfig();
            $config->existingImageStrategy = ImportConfig::EXISTING_IMAGE_STRATEGY_CHECK_IMPORT_DIR;
            if ($this->importHelper->getConfigData('omnyfy_product_import/general/image_strategy') == 'remove') {
                $config->imageStrategy = ImportConfig::IMAGE_STRATEGY_SET;
            }

            $config->resultCallback = function(Product $product) use (&$log, $logger) {
                if ($product->isOk()) {
                    $this->updateAssignedImageStatus($product->getSku(), 'completed');

                    $logger->info(sprintf("%s: success! sku = %s, id = %s", $product->lineNumber, $product->getSku(), $product->id));
                    $log .= sprintf("%s: success! sku = %s, id = %s", $product->lineNumber, $product->getSku(), $product->id);

                }else{
                    $this->updateAssignedImageStatus($product->getSku(), 'error');

                    $logger->info(sprintf("%s: failed! error = %s", $product->lineNumber, implode('; ', $product->getErrors())));
                    $log .= sprintf("%s: failed! error = %s", $product->lineNumber, implode('; ', $product->getErrors()));
                }
            };

            $importer = $this->importerFactory->createImporter($config);
            $i = 1;

            foreach ($imageCollection as $sku => $images) {
                try {
                    $productRepo = $this->productRepository->get($sku);
                    $productType = $productRepo->getTypeId();

                    if ($productType == 'simple') {
                        $product = new SimpleProduct($sku);
                    }elseif ($productType == 'configurable') {
                        $product = new ConfigurableProduct($sku);
                    }else{
                        return;
                    }

                    $global = $product->global();
                    $product->lineNumber = $i;
                    $i++;

                    foreach ($images as $image) {
                        $imageModel = $this->imageFactory->create()->load($image['image_id']);
                        $imageModel->setImportStatus('processing');
                        $imageModel->save();

                        $assignImage = $product->addImage($image['image_path']);
                        $global->setImageGalleryInformation(
                            $assignImage,
                            $image['label'],
                            $image['position'],
                            (bool)$image['enabled']
                        );

                        if (isset($image['roles'])) {
                            $imageRoles = explode(",",$image['roles']);
                            if (count($imageRoles) > 0) {
                                foreach ($imageRoles as $role) {
                                    $global->setImageRole($assignImage, $role);
                                }
                            }
                        }
                    }

                    if ($productType == 'simple') {
                        $importer->importSimpleProduct($product);
                    }elseif ($productType == 'configurable') {
                        $importer->importConfigurableProduct($product);
                    }
                } catch (\Exception $e) {
                    $logger->info("Failed: could not assign image for ". $sku. ". Error: ". $e->getMessage());
                }
            }
            try {
                $importer->flush();
            } catch (\Exception $e) {
                $logger->info($e->getMessage());
            }
        }
        $logger->info("log: ". $log);
    }

    public function addImage($sku, $gallery){
        $imageCache = $this->imageFactory->create();
        $isImageExists = $imageCache->getImage($sku, $gallery['file']);

        if ($isImageExists == null) {
            $roles = "";
            if (isset($gallery['types'])) {
                $roles = implode(",", $gallery['types']);
            }

            $imageCache->setSku($sku);
            $imageCache->setUrlHash(md5($sku."-".$gallery['file']));
            $imageCache->setImageUrl($gallery['file']);
            $imageCache->setImageLabel($gallery['label']);
            $imageCache->setImagePosition($gallery['position']);
            $imageCache->setImageRoles($roles);
            $imageCache->setImageEnabled((int)!$gallery['disabled']);
            $imageCache->save();

        }else{
            $imageId = $isImageExists->getId();
            $imageModel = $this->imageFactory->create()->load($imageId);
            # update image roles if exists
            if (isset($gallery['types']) && count($gallery['types']) > 0) {
                $existingRoles = $imageModel->getImageRoles();
                $newRoles = "";
                if ($existingRoles) {
                    $existingRolesArr = explode(",", $existingRoles);
                    
                    foreach ($gallery['types'] as $role) {
                        if (!in_array($role, $existingRolesArr)) {
                            array_push($existingRolesArr, $role);
                        }
                    }
                    $newRoles = implode(",", $existingRolesArr);
                }else{
                    $newRoles = implode(",", $gallery['types']);
                }

                $imageModel->setImageRoles($newRoles);
                $imageModel->save();
            }
        }
    }

    public function deleteImageCache($sku){
        // delete images for specified SKU from cache table
        $collection = $this->imageCollectionFactory->create()
            ->addFieldToFilter('sku', $sku);

        if ($collection->count() > 0) {
            foreach ($collection as $image) {
                $model = $this->imageFactory->create()->load($image->getId());
                $model->delete();
            }
        }
    }

    private function getImagesToDownload(){
        $images = $this->imageCollectionFactory->create()
            ->addFieldToFilter('download_status', 'pending')
            ->setOrder('id', 'ASC');
        $images->getSelect()->limit(self::LIMIT_PRODUCT_IMAGES);

        return $images;
    }

    private function getImagesToAssign(){
        $images = $this->imageCollectionFactory->create()
            ->addFieldToFilter('download_status', 'completed')
            ->addFieldToFilter('import_status', 'pending')
            ->setOrder('sku', 'ASC');
        $images->getSelect()->limit(self::LIMIT_PRODUCT_IMAGES);

        $arrImages = [];

        if ($images->count() > 0) {
            foreach ($images as $image) {
                $arrImages[$image->getSku()][] = [
                    'image_id' => $image->getId(),
                    'image_path' => $image->getImagePath(),
                    'label' => $image->getImageLabel(),
                    'position' => $image->getImagePosition(),
                    'roles' => $image->getImageRoles(),
                    'enabled' => $image->getImageEnabled()
                ];
            }
        }

        return $arrImages;
    }

    private function updateAssignedImageStatus($sku, $newStatus){
        $images = $this->imageCollectionFactory->create()
            ->addFieldToFilter('import_status', 'processing')
            ->addFieldToFilter('sku', $sku);

        foreach ($images as $image) {
            $image->setData('import_status', $newStatus);
            $image->save();
        }
    }

    private function downloadExternalImages($image, $logger, $failed){
        $imageUrl = $image->getImageUrl();

        $targetDir = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA). DIRECTORY_SEPARATOR
            . 'productimport' . DIRECTORY_SEPARATOR
            . $image->getUrlHash() . DIRECTORY_SEPARATOR;
        $this->file->checkAndCreateFolder($targetDir);

        $parts = parse_url($imageUrl);
        $urlpath = $parts['scheme'].'://'.$parts['host'].$parts['path'];
        $pathinfo = pathinfo($urlpath);
        $newFileName = $targetDir . $pathinfo['basename'];

        if (!$this->fileDriver->isExists($newFileName)) {
            # download image
            $result = $this->file->read($imageUrl, $newFileName);
            if ($result) {
                $image->setImagePath($newFileName);
                $image->setDownloadStatus('completed');
                $logger->info("Success: image downloaded ". $newFileName);
            }else{
                $failed += 1;
                $logger->info("Failed: could not download ". $imageUrl);
            }
        }else{
            # if image is already exists, do not download again
            $image->setImagePath($newFileName);
            $image->setDownloadStatus('completed');
            $logger->info("Success: image downloaded ". $newFileName);
        }
        return [$image, $failed];
    }
}
