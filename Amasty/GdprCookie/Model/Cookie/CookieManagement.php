<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


declare(strict_types=1);

namespace Amasty\GdprCookie\Model\Cookie;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Amasty\GdprCookie\Api\Data\CookieInterface;
use Amasty\GdprCookie\Model\ResourceModel\Cookie;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup;

/**
 * @api
 */
class CookieManagement implements CookieManagementInterface
{
    /**
     * @var Cookie\CollectionFactory
     */
    protected $cookieCollectionFactory;

    /**
     * @var CookieGroup\CollectionFactory
     */
    protected $groupCollectionFactory;

    public function __construct(
        Cookie\CollectionFactory $cookieCollectionFactory,
        CookieGroup\CollectionFactory $groupCollectionFactory
    ) {
        $this->cookieCollectionFactory = $cookieCollectionFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    public function getCookies(int $storeId = 0, int $groupId = 0): array
    {
        $collection = $this->createCookieCollection($storeId);

        if ($groupId) {
            $collection->addFieldToFilter(CookieInterface::GROUP_ID, ['eq' => $groupId]);
        }

        return $collection->getItems();
    }

    public function getEssentialCookies(int $storeId = 0): array
    {
        $collection = $collection = $this->createCookieCollection($storeId)
            ->joinGroup()
            ->addFieldToFilter('groups.' . CookieGroupsInterface::IS_ESSENTIAL, ['eq' => 1]);

        return $collection->getItems();
    }

    public function getNotAssignedCookiesToGroups(int $storeId = 0, array $groupIds = []): array
    {
        $collection = $this->createCookieCollection($storeId);

        if ($groupIds) {
            $collection->addFieldToFilter(CookieInterface::GROUP_ID, ['nin' => $groupIds]);
        }

        return $collection->getItems();
    }

    public function getGroups(int $storeId = 0, array $groupIds = []): array
    {
        $collection = $this->groupCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addFieldToFilter(CookieGroupsInterface::IS_ENABLED, ['eq' => 1]);
        $collection->setOrder(CookieGroupsInterface::SORT_ORDER, $collection::SORT_ORDER_ASC);
        $collection->setOrder(CookieGroupsInterface::ID, $collection::SORT_ORDER_ASC);

        if ($groupIds) {
            $collection->addFieldToFilter(CookieGroupsInterface::ID, ['in' => $groupIds]);
        }

        return $collection->getItems();
    }

    protected function createCookieCollection(int $storeId = 0)
    {
        $collection = $this->cookieCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addFieldToFilter(CookieInterface::IS_ENABLED, ['eq' => 1]);

        return $collection;
    }
}
