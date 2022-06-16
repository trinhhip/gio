<?php
declare(strict_types=1);

namespace Omnyfy\VendorAuth\Model;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Model\CredentialsValidator;
use Magento\User\Model\User as UserModel;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Framework\App\ResourceConnection;
use Omnyfy\VendorAuth\Api\Data\TokenInterfaceFactory;
use Magento\Integration\Model\ResourceModel\Integration\CollectionFactory as IntegrationCollectionFactory;
use Magento\Integration\Api\IntegrationServiceInterface;

class VendorTokenService implements \Omnyfy\VendorAuth\Api\VendorTokenServiceInterface
{
    /**
     * User Model
     *
     * @var UserModel
     */
    private $userModel;

    /**
     * @var \Magento\Integration\Model\CredentialsValidator
     */
    private $validatorHelper;

    /**
     * @var RequestThrottler
     */
    private $requestThrottler;

    private $connection;

    private $tokenInterfaceFactory;

    private $integrationCollectionFactory;

    private $integrationService;


    public function __construct(
        UserModel $userModel,
        CredentialsValidator $validatorHelper,
        ResourceConnection $connection,
        TokenInterfaceFactory $tokenInterfaceFactory,
        IntegrationCollectionFactory $integrationCollectionFactory,
        IntegrationServiceInterface $integrationService
    ) {
        $this->userModel = $userModel;
        $this->validatorHelper = $validatorHelper;
        $this->connection = $connection;
        $this->tokenInterfaceFactory = $tokenInterfaceFactory;
        $this->integrationCollectionFactory = $integrationCollectionFactory;
        $this->integrationService = $integrationService;
    }

    /**
     * @inheritdoc
     */
    public function getVendorAccessToken($username, $password)
    {
        $this->validatorHelper->validate($username, $password);
        $this->getRequestThrottler()->throttle($username, RequestThrottler::USER_TYPE_ADMIN);
        $this->userModel->login($username, $password);
        if (!$this->userModel->getId()) {
            $this->getRequestThrottler()->logAuthenticationFailure($username, RequestThrottler::USER_TYPE_ADMIN);
            /*
             * This message is same as one thrown in \Magento\Backend\Model\Auth to keep the behavior consistent.
             * Constant cannot be created in Auth Model since it uses legacy translation that doesn't support it.
             * Need to make sure that this is refactored once exception handling is updated in Auth Model.
             */
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }
        $this->getRequestThrottler()->resetAuthenticationFailuresCount($username, RequestThrottler::USER_TYPE_ADMIN);
        if (empty($this->getVendorId())) {
            throw new AuthenticationException(
                __('The account is not an vendor account.')
            );
        }
        $integrationCollection = $this->integrationCollectionFactory->create()
            ->addFieldToFilter('vendor_id', $this->getVendorId());
        if (empty($integrationCollection->getSize())) {
            throw new AuthenticationException(
                __('There are no vendor integration token for this account.')
            );
        }
        $integrationData = $this->integrationService->get($integrationCollection->getLastItem()->getId());
        $tokenObject = $this->tokenInterfaceFactory->create();
        return $tokenObject->setToken($integrationData['token']);
    }

    /**
     * Get request throttler instance
     *
     * @return RequestThrottler
     * @deprecated 100.0.4
     */
    private function getRequestThrottler()
    {
        if (!$this->requestThrottler instanceof RequestThrottler) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(RequestThrottler::class);
        }
        return $this->requestThrottler;
    }

    private function getVendorId()
    {
        $conn = $this->connection->getConnection();
        $table = $conn->getTableName('omnyfy_vendor_vendor_admin_user');

        $select = $conn->select()->from(
            $table,
            'vendor_id'
        )->where("user_id = ?", $this->userModel->getId());
        return $conn->fetchOne($select);
    }
}
