<?php


namespace OmnyfyCustomzation\Smtp\Plugin\Mail;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class SendFriend
{
    const GENERAL_EMAIL = 'trans_email/ident_support/email';
    const GENERAL_NAME = 'trans_email/ident_support/name';
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function beforeSetSender(\Magento\SendFriend\Model\SendFriend $subject, $sender)
    {
        if (isset($sender['email']) && isset($sender['name'])) {
            $sender['email'] = $this->scopeConfig->getValue(self::GENERAL_EMAIL, ScopeInterface::SCOPE_STORE);
            $sender['name'] = $this->scopeConfig->getValue(self::GENERAL_NAME, ScopeInterface::SCOPE_STORE);
        }
        return [$sender];
    }
}
