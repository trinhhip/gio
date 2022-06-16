<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Customer;

use Amasty\Gdpr\Controller\Result\CsvFactory;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\CustomerData;
use Amasty\Gdpr\Model\GuestOrderProvider;
use Magento\Customer\Model\Authentication;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;

class DownloadCsv extends Action implements HttpPostActionInterface
{
    const CSV_FILE_NAME = 'personal-data.csv';

    /**
     * @var CustomerData
     */
    private $customerData;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var CsvFactory
     */
    private $csvFactory;

    /**
     * @var GuestOrderProvider
     */
    private $guestOrderProvider;

    public function __construct(
        Context $context,
        CustomerData $customerData,
        Session $customerSession,
        LoggerInterface $logger,
        File $fileDriver,
        Authentication $authentication,
        FormKeyValidator $formKeyValidator,
        Config $configProvider,
        CsvFactory $csvFactory,
        GuestOrderProvider $guestOrderProvider
    ) {
        parent::__construct($context);
        $this->customerData = $customerData;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->fileDriver = $fileDriver;
        $this->formKeyValidator = $formKeyValidator;
        $this->authentication = $authentication;
        $this->configProvider = $configProvider;
        $this->csvFactory = $csvFactory;
        $this->guestOrderProvider = $guestOrderProvider;
    }

    public function execute()
    {
        $errorMessage = '';

        if (!$this->configProvider->isAllowed(Config::DOWNLOAD)) {
            $errorMessage = __('Access denied.');
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $errorMessage = __('Invalid Form Key. Please refresh the page.');
        }

        if ($errorMessage) {
            $this->messageManager->addErrorMessage($errorMessage);
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        $incrementId = null;
        $customerId = (int)$this->customerSession->getCustomerId();

        try {
            if ($customerId) {
                $customerPass = $this->getRequest()->getParam('current_password');
                $this->authentication->authenticate($customerId, $customerPass);
                $data = $this->customerData->getPersonalData($customerId);
            } else {
                $incrementId = $this->guestOrderProvider->getGuestOrder()->getIncrementId();
                $data = $this->customerData->getGuestPersonalData($incrementId);
            }
        } catch (AuthenticationException $e) {
            $this->messageManager->addErrorMessage(__('Wrong Password. Please recheck it.'));
            return $this->_redirect($this->_redirect->getRefererUrl());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong.')
            );
            $this->logger->critical($e);
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        $response = $this->csvFactory->create(['fileName' => self::CSV_FILE_NAME]);
        $response->setData($data);

        return $response;
    }
}
