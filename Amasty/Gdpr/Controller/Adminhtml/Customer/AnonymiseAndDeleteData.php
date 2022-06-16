<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Controller\Adminhtml\Customer;

class AnonymiseAndDeleteData extends Anonymise
{
    public function execute()
    {
        if ($customerId = (int)$this->_request->getParam('customerId')) {
            try {
                $warningMessage = '';

                if ($notCompletedOrderIds = $this->getNonCompletedOrderIds($customerId)) {
                    $warningMessage = __(
                        'Personal data cannot be deleted right now, because the customer has '
                        . 'non-completed order(s): %1',
                        implode(' ', $notCompletedOrderIds)
                    );
                }

                if ($this->isCustomerHasActiveGiftRegistry($customerId)) {
                    $warningMessage = __(
                        'Personal data cannot be deleted right now, because the customer has active Gift Registry.'
                    );
                }

                if ($warningMessage) {
                    $this->messageManager->addWarningMessage($warningMessage);
                } else {
                    $this->anonymizer->approveDeleteRequest($customerId);
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error has occurred: %1', $e->getMessage()));
            }
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
