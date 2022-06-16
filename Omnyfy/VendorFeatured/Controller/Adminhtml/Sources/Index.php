<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 5/12/2019
 * Time: 4:03 PM
 */

namespace Omnyfy\VendorFeatured\Controller\Adminhtml\Sources;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $_sourceCollectionFactory;
    protected $_tagCollectionFactory;
    protected $_featuredCollectionFactory;
    protected $_logger;

    public function __construct(
        Action\Context $context,
        \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Omnyfy\VendorFeatured\Model\ResourceModel\VendorFeatured\CollectionFactory $featuredCollectionFactory,
        \Omnyfy\VendorFeatured\Model\ResourceModel\VendorFeaturedTag\CollectionFactory $tagCollectionFactory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_sourceCollectionFactory = $sourceCollectionFactory;
        $this->_featuredCollectionFactory = $featuredCollectionFactory;
        $this->_tagCollectionFactory = $tagCollectionFactory;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $responseData = [
            'error' => true
        ];

        try {
            $vendorId = $this->_request->getParam('vendorId', null);
            if ($vendorId) {
                $sourceCollection = $this->_sourceCollectionFactory->create();
                $sourceCollection->addFieldToFilter('vendor_id',['eq' => $vendorId]);

                $options = [];

                foreach($sourceCollection as $location){
                    $options[] = [
                        'label' => $location->getData('name'),
                        'value' => $location->getData('source_code'),
                    ];
                }


                $vendorFeaturedId = $this->_request->getParam('vendorFeaturedId', null);

                if (!empty($vendorFeaturedId)) {
                    try {
                        /** @var \Omnyfy\VendorFeatured\Model\ResourceModel\VendorFeatured\Collection $featuredCollection */
                        $featuredCollection = $this->_featuredCollectionFactory->create();
                        $featuredCollection->addFieldToFilter('vendor_featured_id', ['eq' => $vendorFeaturedId]);

                        if ($featuredCollection->count() > 0) {
                            $responseData['source'] = $featuredCollection->getItemById($vendorFeaturedId)->getData('source_code');
                        } else {
                            $responseData['source'] = null;
                        }
                    } catch (\Exception $exception){
                        $responseData['source'] = 0;
                    }

                    /** @var \Omnyfy\VendorFeatured\Model\ResourceModel\VendorFeaturedTag\Collection $collection */
                    $collection = $this->_tagCollectionFactory->create();
                    $collection->addFieldToSelect('vendor_tag_id');
                    $collection->addFieldToFilter('vendor_featured_id', ['eq' => $vendorFeaturedId]);
                    $tagsIds = [];
                    foreach ($collection as $tag) {
                        $tagsIds[] = $tag->getData('vendor_tag_id');
                    }

                    $responseData['tags'] = $tagsIds;
                }

                $responseData['error'] = false;
                $responseData['options'] = $options;

            }
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
            $this->_logger->debug($e->getTraceAsString());
        }

        return $result->setData($responseData);
    }
}