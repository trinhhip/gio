<?php
namespace Omnyfy\VendorGallery\Block\Adminhtml\Album\Helper\Form\Gallery;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\App\ObjectManager;
use Magento\Backend\Block\Media\Uploader;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\DataProviders\ImageUploadConfig as ImageUploadConfigDataProvider;
use Magento\MediaStorage\Helper\File\Storage\Database;

class Content extends \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content
{
    protected $_template = 'Omnyfy_VendorGallery::album/helper/gallery.phtml';

    /**
     * @var \Omnyfy\VendorGallery\Model\Album\Item\Config
     */
    protected $albumConfig;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var ImageUploadConfigDataProvider
     */
    private $imageUploadConfigDataProvider;

    /**
     * @var Database
     */
    private $fileStorageDatabase;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param array $data
     * @param ImageUploadConfigDataProvider $imageUploadConfigDataProvider
     * @param Database $fileStorageDatabase
     * @param JsonHelper|null $jsonHelper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        array $data = [],
        ImageUploadConfigDataProvider $imageUploadConfigDataProvider = null,
        Database $fileStorageDatabase = null,
        ?JsonHelper $jsonHelper = null,
        \Omnyfy\VendorGallery\Model\Album\Item\Config $albumConfig,
        \Magento\Framework\Registry $registry
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_mediaConfig = $mediaConfig;
        $data['jsonHelper'] = $jsonHelper ?? ObjectManager::getInstance()->get(JsonHelper::class);
        $this->imageUploadConfigDataProvider = $imageUploadConfigDataProvider
            ?: ObjectManager::getInstance()->get(ImageUploadConfigDataProvider::class);
        $this->fileStorageDatabase = $fileStorageDatabase
            ?: ObjectManager::getInstance()->get(Database::class);
        $this->albumConfig = $albumConfig;
        $this->registry = $registry;
        parent::__construct($context, $jsonEncoder, $mediaConfig, $data, $imageUploadConfigDataProvider, $fileStorageDatabase, $jsonHelper);
    }

    protected function _prepareLayout()
    {
        $this->addChild(
            'uploader',
            \Magento\Backend\Block\Media\Uploader::class,
            ['image_upload_config_data' => $this->imageUploadConfigDataProvider]
        );

        $this->getUploader()->getConfig()->setUrl(
            $this->_urlBuilder->getUrl('vendor_gallery/album/upload')
        )->setFileField(
            'image'
        )->setFilters(
            [
                'images' => [
                    'label' => __('Images (.gif, .jpg, .png)'),
                    'files' => ['*.gif', '*.jpg', '*.jpeg', '*.png'],
                ],
            ]
        );

        $this->_eventManager->dispatch('vendor_gallery_prepare_layout', ['block' => $this]);

        return $this;
    }

    public function getImageTypes()
    {
        $currentAlbum = $this->registry->registry('current_album');

        if (empty($currentAlbum->getData())) {
            $imageType = ['thumbnail' => [
                'code' => 'thumbnail',
                'value' => '',
                'label' => 'Thumbnail',
                'scope' => 'STORE VIEW',
                'name' => 'album[thumbnail]'
            ]];
        } else {
            $imageType = ['thumbnail' => [
                'code' => 'thumbnail',
                'value' =>  $currentAlbum->getThumbnailValue(),
                'label' => 'Thumbnail',
                'scope' => 'STORE VIEW',
                'name' => 'album[thumbnail]'
            ]];
        }

        return $imageType;
    }

    public function getMediaAttributes()
    {
        return [];
    }

    public function getImagesJson()
    {
        $value = $this->getElement()->getImages();
        if (is_array($value) &&
            count($value)
        ) {
            $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $images = $this->sortImagesByPosition($value);
            foreach ($images as &$image) {
                $image['url'] = $this->albumConfig->getMediaUrl($image['url']);
                try {
                    $fileHandler = $mediaDir->stat($this->albumConfig->getMediaPath($image['file']));
                    $image['size'] = $fileHandler['size'];
                } catch (FileSystemException $e) {
                    $image['url'] = $this->getImageHelper()->getDefaultPlaceholderUrl('small_image');
                    $image['size'] = 0;
                    $this->_logger->warning($e);
                }
            }
            return $this->_jsonEncoder->encode($images);
        }
        return '[]';
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     * @deprecated
     */
    private function getImageHelper()
    {
        if ($this->imageHelper === null) {
            $this->imageHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Helper\Image');
        }
        return $this->imageHelper;
    }

    /**
     * Sort images array by position key
     *
     * @param array $images
     * @return array
     */
    private function sortImagesByPosition($images)
    {
        if (is_array($images)) {
            usort($images, function ($imageA, $imageB) {
                return ($imageA['position'] < $imageB['position']) ? -1 : 1;
            });
        }
        return $images;
    }
}
