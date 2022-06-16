<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\OptionSource\Cookie;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;

class Groups implements OptionSourceInterface
{
    const NONE_GROUP_ID = 0;

    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        CookieManagementInterface $cookieManagement,
        RequestInterface $request
    ) {
        $this->cookieManagement = $cookieManagement;
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $storeId = (int)$this->request->getParam('store');
        $allGroups = $this->cookieManagement->getGroups($storeId);
        $groups = [
            [
                'value' => self::NONE_GROUP_ID,
                'label' => __('None')
            ]
        ];

        foreach ($allGroups as $group) {
            $groups[] = ['value' => $group->getId(), 'label' => $group->getName()];
        }

        return $groups;
    }
}
