<?php
namespace Omnyfy\VendorAuth\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as Date;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;
use Magento\Integration\Model\Oauth\Token;

class TokenUserContext extends \Magento\Webapi\Model\Authorization\TokenUserContext
{
    /**
     * @var \Omnyfy\VendorAuth\Helper\VendorApi
     */
    protected $vendorApiHelper;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var OauthHelper
     */
    private $oauthHelper;

    /**
     * Initialize dependencies.
     *
     * TokenUserContext constructor.
     * @param \Magento\Framework\Webapi\Request $request
     * @param \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
     * @param \Magento\Framework\Stdlib\DateTime|null $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\DateTime|null $date
     * @param \Magento\Integration\Helper\Oauth\Data|null $oauthHelper
     */
    public function __construct(
        \Magento\Framework\Webapi\Request $request,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime = null,
        \Magento\Framework\Stdlib\DateTime\DateTime $date = null,
        \Magento\Integration\Helper\Oauth\Data $oauthHelper = null
    ){
        $this->vendorApiHelper = $vendorApiHelper;
        $this->dateTime = $dateTime ?: ObjectManager::getInstance()->get(
            DateTime::class
        );
        $this->date = $date ?: ObjectManager::getInstance()->get(
            Date::class
        );
        $this->oauthHelper = $oauthHelper ?: ObjectManager::getInstance()->get(
            OauthHelper::class
        );
        parent::__construct($request, $tokenFactory, $integrationService, $dateTime, $date, $oauthHelper);
    }
    /**
     * Finds the bearer token and looks up the value.
     *
     * @return void
     */
    protected function processRequest()
    {
        if ($this->isRequestProcessed) {
            return;
        }

        $authorizationHeaderValue = $this->request->getHeader('Authorization');
        if (!$authorizationHeaderValue) {
            $this->isRequestProcessed = true;
            return;
        }

        $headerPieces = explode(" ", $authorizationHeaderValue);
        if (count($headerPieces) !== 2) {
            $this->isRequestProcessed = true;
            return;
        }

        $tokenType = strtolower($headerPieces[0]);
        if ($tokenType !== 'bearer') {
            $this->isRequestProcessed = true;
            return;
        }

        $bearerToken = $headerPieces[1];
        $token = $this->tokenFactory->create()->loadByToken($bearerToken);

        if (!$token->getId() || $token->getRevoked() || $this->isTokenExpired($token)) {
            $this->isRequestProcessed = true;
            return;
        }

        if ($token->getId()) {
            $verify = $this->vendorApiHelper->verifyEndpoint();
            if (!$verify) {
                $this->isRequestProcessed = true;
                return;
            }
        }

        $this->setUserDataViaToken($token);
        $this->isRequestProcessed = true;
    }

    /**
     * Check if token is expired.
     *
     * @param Token $token
     * @return bool
     */
    private function isTokenExpired(Token $token): bool
    {
        if ($token->getUserType() == UserContextInterface::USER_TYPE_ADMIN) {
            $tokenTtl = $this->oauthHelper->getAdminTokenLifetime();
        } elseif ($token->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            $tokenTtl = $this->oauthHelper->getCustomerTokenLifetime();
        } else {
            // other user-type tokens are considered always valid
            return false;
        }

        if (empty($tokenTtl)) {
            return false;
        }

        if ($this->dateTime->strToTime($token->getCreatedAt()) < ($this->date->gmtTimestamp() - $tokenTtl * 3600)) {
            return true;
        }
        return false;
    }
}
