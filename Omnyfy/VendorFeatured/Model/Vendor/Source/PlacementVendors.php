<?php
namespace Omnyfy\VendorFeatured\Model\Vendor\Source;

class PlacementVendors implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory
     */
    protected $vendorCollectionFactory;

    /**
     * @var \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement\CollectionFactory
     */
    protected $bannerCollectionFactory;

    /**
     * Vendors constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory $vendorCollectionFactory
     * @param \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement\CollectionFactory $bannerCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory $vendorCollectionFactory,
        \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement\CollectionFactory $bannerCollectionFactory
    )
    {
        $this->registry = $registry;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value['label'],
                'value' => $key,
                'disabled' => $value['disabled']
            ];
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $labelArray = [];
        $placedVendor = [];

        $currentBanner = $this->registry->registry('omnyfy_vendorfeatured_spotlight_banner');
        if (isset($currentBanner) && $currentBanner!=null) {
            $bannerId = $currentBanner->getBannerId();
            $banners = $this->bannerCollectionFactory->create()
                ->addFieldToFilter('banner_id', array('neq' => $bannerId))
                ->addFieldToFilter('vendor_ids', array('notnull' => true))
            ;
            if (count($banners)) {
                foreach ($banners as $banner) {
                    $vendorIds = $banner->getVendorIds();
                    if (strpos($vendorIds, ",") !== false) {
                        $ids = explode(",", $vendorIds);
                        foreach ($ids as $id) {
                            $placedVendor[] = [
                                'vendor_id' => $id,
                                'banner_name' => $banner->getBannerName()
                            ];
                        }
                    }else{
                        $placedVendor[] = [
                            'vendor_id' => $vendorIds,
                            'banner_name' => $banner->getBannerName()
                        ];
                    }
                }
            }
        }

        /** @var \Omnyfy\Vendor\Model\Resource\Vendor\Collection $vendorCollection */
        $vendorCollection = $this->vendorCollectionFactory->create();
        $vendorCollection->load();

        if($vendorCollection->count() > 0) {
            foreach ($vendorCollection as $vendor){
                $search = array_search($vendor->getId(), array_column($placedVendor, 'vendor_id'));
                if ($search !== false) {
                    $labelArray[$vendor->getId()]['label'] = $vendor->getName(). " [Placement already assigned to \"" . $placedVendor[$search]['banner_name'] ."\"]";
                    $labelArray[$vendor->getId()]['disabled'] = true;
                }else{
                    $labelArray[$vendor->getId()]['label'] = $vendor->getName();
                    $labelArray[$vendor->getId()]['disabled'] = false;
                }
            }
        }
        return $labelArray;
    }
}