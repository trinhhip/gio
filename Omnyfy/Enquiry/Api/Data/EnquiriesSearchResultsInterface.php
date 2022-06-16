<?php


namespace Omnyfy\Enquiry\Api\Data;

interface EnquiriesSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get enquiries list.
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface[]
     */
    public function getItems();

    /**
     * Set vendor_id list.
     * @param \Omnyfy\Enquiry\Api\Data\EnquiriesInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
