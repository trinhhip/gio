<?php
namespace Omnyfy\Vendor\Plugin\Amasty\AdminActionLog\Logging;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterfaceFactory;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Api\VisitHistoryEntryRepositoryInterface;
use Amasty\AdminActionsLog\Model\Admin\SessionUserDataProvider;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryDetail;
use Magento\Framework\Stdlib\DateTime\DateTime;

class LayoutPlugin
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var SessionUserDataProvider
     */
    private $userDataProvider;

    /**
     * @var ObjectDataStorageInterface
     */
    private $dataStorage;

    /**
     * @var VisitHistoryDetailInterfaceFactory
     */
    private $detailFactory;

    /**
     * @var VisitHistoryEntryRepositoryInterface
     */
    private $historyEntryRepository;

    public function __construct(
        DateTime $dateTime,
        MetadataInterface $metadata,
        SessionUserDataProvider $userDataProvider,
        ObjectDataStorageInterface $dataStorage,
        VisitHistoryDetailInterfaceFactory $detailFactory,
        VisitHistoryEntryRepositoryInterface $historyEntryRepository
    ) {
        $this->dateTime = $dateTime;
        $this->metadata = $metadata;
        $this->userDataProvider = $userDataProvider;
        $this->dataStorage = $dataStorage;
        $this->detailFactory = $detailFactory;
        $this->historyEntryRepository = $historyEntryRepository;
    }

    public function aroundExecute($subject, callable $process) {
        if (!$context = $this->metadata->getObject()) {
            return;
        }

        $sessionId = $this->userDataProvider->getSessionId();
        $historyEntry = $this->historyEntryRepository->getBySessionId($sessionId);
        $details = $historyEntry->getVisitHistoryDetails();
        $lastDetail = end($details) ?: $this->detailFactory->create();

        $ignoreAction = 'admin/import/validate';
        $isIgnore = strpos($context->getUrlBuilder()->getCurrentUrl(), $ignoreAction);

        if (!$isIgnore && $lastDetail->getPageUrl() !== $context->getUrlBuilder()->getCurrentUrl()) {
            $storageKey = $this->userDataProvider->getUserName() . 'StayDuration';
            $currentTimeStamp = $this->dateTime->gmtTimestamp();
            $lastDetailStayDuration = $this->dataStorage->isExists($storageKey)
                ? $currentTimeStamp - ($this->dataStorage->get($storageKey)['timestamp'] ?? 0)
                : 0;
            $lastDetail->setStayDuration((int)$lastDetailStayDuration);
            $this->dataStorage->set($storageKey, ['timestamp' => $currentTimeStamp]);
            $details[] = $this->detailFactory->create(['data' => [
                VisitHistoryDetail::PAGE_NAME => $context->getPageConfig()->getTitle()->get(),
                VisitHistoryDetail::PAGE_URL => $context->getUrlBuilder()->getCurrentUrl()
            ]]);
            $historyEntry->setVisitHistoryDetails($details);
            $this->historyEntryRepository->save($historyEntry);
        }
    }
}
