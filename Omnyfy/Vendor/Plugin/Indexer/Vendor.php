<?php

namespace Omnyfy\Vendor\Plugin\Indexer;

use Magento\Framework\Model\AbstractModel;
use Omnyfy\Vendor\Model\Indexer\Vendor\Flat\Processor;
use Omnyfy\Vendor\Model\Resource\Vendor as VendorResource;

class Vendor {

    private $processor;

    public function __construct(
        Processor $processor
    )
    {
        $this->processor = $processor;
    }

    /**
     * Reindex on product save.
     *
     * @param VendorResource $productResource
     * @param \Closure $proceed
     * @param AbstractModel $vendor
     * @return VendorResource
     * @throws \Exception
     */
    public function aroundSave(VendorResource $productResource, \Closure $proceed, AbstractModel $vendor)
    {
        return $this->addCommitCallback($productResource, $proceed, $vendor);
    }

    private function addCommitCallback(VendorResource $vendorResource, \Closure $proceed, AbstractModel $vendor)
    {
        try {
            $vendorResource->beginTransaction();
            $result = $proceed($vendor);
            $vendorResource->addCommitCallback(function () use ($vendor) {
                $this->processor->reindexRow($vendor->getEntityId());
            });
            $vendorResource->commit();
        } catch (\Exception $e) {
            $vendorResource->rollBack();
            throw $e;
        }

        return $result;
    }
}