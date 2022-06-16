<?php


namespace Omnyfy\Enquiry\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface EnquiryMessagesRepositoryInterface
{


    /**
     * Save enquiry_messages
     * @param \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface $enquiryMessages
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface $enquiryMessages
    );

    /**
     * Retrieve enquiry_messages
     * @param string $enquiryMessagesId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($enquiryMessagesId);

    /**
     * Retrieve enquiry_messages matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete enquiry_messages
     * @param \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface $enquiryMessages
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface $enquiryMessages
    );

    /**
     * Delete enquiry_messages by ID
     * @param string $enquiryMessagesId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($enquiryMessagesId);
}
