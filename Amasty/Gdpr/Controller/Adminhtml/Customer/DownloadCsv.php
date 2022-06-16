<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\Customer;

use Amasty\Gdpr\Controller\Result\CsvFactory;
use Amasty\Gdpr\Model\CustomerData;
use Magento\Backend\App\Action;
use Magento\Customer\Controller\AbstractAccount as AbstractAccountAction;

class DownloadCsv extends AbstractAccountAction
{
    /**
     * @var CsvFactory
     */
    private $csvFactory;

    /**
     * @var CustomerData
     */
    private $customerData;

    public function __construct(
        Action\Context $context,
        CsvFactory $csvFactory,
        CustomerData $customerData
    ) {
        parent::__construct($context);
        $this->csvFactory = $csvFactory;
        $this->customerData = $customerData;
    }

    public function execute()
    {
        $customerId = (int)$this->getRequest()->getParam('customerId');
        $data = $this->customerData->getPersonalData($customerId);
        $response = $this->csvFactory->create(['fileName' => 'personal-data.csv']);
        $response->setData($data);

        return $response;
    }
}
