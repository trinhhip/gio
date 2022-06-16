<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-customer-segment
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CustomerSegment\Service\Candidate\Finder;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Mirasvit\CustomerSegment\Api\Data\CandidateInterface;
use Mirasvit\CustomerSegment\Api\Data\CandidateInterfaceFactory;
use Mirasvit\CustomerSegment\Api\Data\Segment\StateInterface;
use Mirasvit\CustomerSegment\Api\Data\SegmentInterface;
use Mirasvit\CustomerSegment\Api\Service\Candidate\FinderInterface;

class CustomerFinder implements FinderInterface
{
    /**
     * Finder code.
     * @var string
     */
    const CODE = 'customer';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CandidateInterfaceFactory
     */
    private $candidateFactory;

    /**
     * @var CustomerCollection
     */
    private $customerCollection;

    /**
     * CustomerFinder constructor.
     * @param CandidateInterfaceFactory $candidateFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerCollection $customerCollection
     */
    public function __construct(
        CandidateInterfaceFactory $candidateFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerCollection $customerCollection
    ) {
        $this->customerRepository = $customerRepository;
        $this->candidateFactory   = $candidateFactory;
        $this->customerCollection = $customerCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Registered Customers');
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return self::CODE;
    }

    /**
     * Can process or not.
     *
     * @param int $segmentType
     *
     * @return bool
     */
    public function canProcess($segmentType)
    {
        if ($segmentType == SegmentInterface::TYPE_CUSTOMER || $segmentType == SegmentInterface::TYPE_ALL) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function find($segmentType, $websiteId, StateInterface $state)
    {
        $candidates = [];

        if (!$this->canProcess($segmentType)) {
            return $candidates;
        }

        // filter by website
        $this->customerCollection->addFieldToFilter('website_id', $websiteId);

        $allCustomersCount = $this->customerCollection
            ->getConnection()
            ->fetchOne($this->customerCollection->getSelectCountSql());

        $pageSize    = $state->getIndex() ? : $state->getLimit();
        $currentPage = $state->getSize() / $pageSize + 1;

        $this->customerCollection->setPageSize($pageSize);
        $this->customerCollection->setCurPage($currentPage);

        // save customer total size
        $state->setCustomerTotalSize($allCustomersCount);

        // stop indexing customers if state size greater than size of customer collection
        if ($state->getSize() >= $allCustomersCount) {
            $state->finishStep($this->getCode());

            return $candidates;
        }

        return $this->createCandidates($this->customerCollection->getItems());
    }

    /**
     * Create candidates from customers.
     *
     * @param array       $items
     * @param StateInterface $state
     *
     * @return array
     */
    public function createCandidates(array $items = [], StateInterface $state = null)
    {
        $candidates = [];
        foreach ($items as $customer) {
            /** @var CandidateInterface $candidate */
            $candidate    = $this->candidateFactory->create();
            $customer->load($customer->getId());
            /** @var CustomerInterface|AbstractSimpleObject $customer */
            $customerData = $customer instanceof Customer
                ? $customer->__toArray()
                : $customer->getData();

            $candidates[] = $candidate->setData($customerData)
                ->setCustomerId($customer->getId())
                ->setName("{$customer->getFirstname()} {$customer->getLastname()}");
        }

        return $candidates;
    }
}
