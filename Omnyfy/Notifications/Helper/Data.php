<?php
namespace Omnyfy\Notifications\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package Omnyfy\Notifications\Helper
 */
class Data extends AbstractHelper
{
    /**
     *
     */
    const XML_PATH_OMNYFY_NOTIFICATIONS = 'omnyfy_notifications/notification/enabled';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getNotificationConfig()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_OMNYFY_NOTIFICATIONS);
    }
}