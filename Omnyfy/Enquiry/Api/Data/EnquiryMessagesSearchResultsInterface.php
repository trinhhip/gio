<?php


namespace Omnyfy\Enquiry\Api\Data;

interface EnquiryMessagesSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get enquiry_messages list.
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface[]
     */
    public function getItems();

    /**
     * Set from_id list.
     * @param \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
