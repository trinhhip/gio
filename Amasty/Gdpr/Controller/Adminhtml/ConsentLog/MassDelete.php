<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\ConsentLog;

use Amasty\Gdpr\Controller\Adminhtml\AbstractWithConsent;
use Amasty\Gdpr\Model\ResourceModel\WithConsent\CollectionFactory as ConsentLogCollectionFactory;
use Amasty\Gdpr\Model\Repository\WithConsentRepository;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends AbstractWithConsent
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var ConsentLogCollectionFactory
     */
    private $consentLogCollectionFactory;

    /**
     * @var WithConsentRepository
     */
    private $withConsentRepository;

    public function __construct(
        Filter $filter,
        Action\Context $context,
        ConsentLogCollectionFactory $consentLogCollectionFactory,
        WithConsentRepository $withConsentRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->consentLogCollectionFactory = $consentLogCollectionFactory;
        $this->withConsentRepository = $withConsentRepository;
    }

    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider();
        /** @var \Amasty\Gdpr\Model\ResourceModel\WithConsent\Collection $collection */
        $collection = $this->filter->getCollection($this->consentLogCollectionFactory->create());

        if ($collection->getSize()) {
            foreach ($collection->getItems() as $consentLog) {
                try {
                    $this->withConsentRepository->delete($consentLog);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }

        $this->messageManager->addSuccessMessage(__('Consent logs was successfully removed.'));

        $this->_redirect('*/*');
    }
}
