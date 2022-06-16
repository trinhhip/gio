<?php


namespace Omnyfy\Enquiry\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface EnquiriesRepositoryInterface
{


    /**
     * @param Data\EnquiriesInterface $enquiries
     * @param string[] $enquiryMessages
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function save(
        \Omnyfy\Enquiry\Api\Data\EnquiriesInterface $enquiries,
        $enquiryMessages = array()
    );

    /**
     * Retrieve enquiries
     * @param string $enquiriesId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($enquiriesId);

    /**
     * Retrieve enquiries matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete enquiries
     * @param \Omnyfy\Enquiry\Api\Data\EnquiriesInterface $enquiries
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Omnyfy\Enquiry\Api\Data\EnquiriesInterface $enquiries
    );

    /**
     * Delete enquiries by ID
     * @param string $enquiriesId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($enquiriesId);
}
