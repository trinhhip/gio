<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\Cron;

use Amasty\Gdpr\Api\Data\DeleteRequestInterface;
use Amasty\Gdpr\Api\DeleteRequestRepositoryInterface;
use Amasty\Gdpr\Model\CleaningDate;
use Amasty\Gdpr\Model\DeleteRequest\DeleteRequestSource;
use Amasty\Gdpr\Model\DeleteRequestFactory;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory as DeleteRequestCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Sales\Api\Data\OrderInterface;

class AutomaticRequests
{
    /**
     * @var CleaningDate
     */
    private $cleaningDate;

    /**
     * @var DeleteRequestRepositoryInterface
     */
    private $deleteRequestRepository;

    /**
     * @var DeleteRequestFactory
     */
    private $deleteRequestFactory;

    /**
     * @var DeleteRequestCollectionFactory
     */
    private $deleteRequestCollectionFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        CleaningDate $cleaningDate,
        DeleteRequestRepositoryInterface $deleteRequestRepository,
        DeleteRequestFactory $deleteRequestFactory,
        DeleteRequestCollectionFactory $deleteRequestCollectionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->cleaningDate = $cleaningDate;
        $this->deleteRequestRepository = $deleteRequestRepository;
        $this->deleteRequestFactory = $deleteRequestFactory;
        $this->deleteRequestCollectionFactory = $deleteRequestCollectionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function orderProcess()
    {
        if (!$dateForRemove = $this->cleaningDate->getPersonalDataDeletionDate()) {
            return;
        }

        $alreadyDeletedCustomers = $this->deleteRequestCollectionFactory->create()
            ->addFieldToSelect(DeleteRequestInterface::CUSTOMER_ID)
            ->getData();

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from($this->resourceConnection->getTableName('sales_order'))
            ->reset(Select::COLUMNS)
            ->columns([OrderInterface::CUSTOMER_ID, 'MAX(' . OrderInterface::CREATED_AT . ') as lastOrderDate'])
            ->group(OrderInterface::CUSTOMER_ID)
            ->having('lastOrderDate <= ?', $dateForRemove);

        if ($alreadyDeletedCustomers) {
            $select->where(OrderInterface::CUSTOMER_ID . ' NOT IN (?)', $alreadyDeletedCustomers);
        }
        $customerForDeletion = $connection->fetchAll($select);
        foreach ($customerForDeletion as $customerData) {
            $request = $this->deleteRequestFactory->create();
            $request->setCustomerId($customerData[OrderInterface::CUSTOMER_ID]);
            $request->setGotFrom(DeleteRequestSource::AUTOMATIC);
            $this->deleteRequestRepository->save($request);
        }
    }
}
