<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


declare(strict_types=1);

namespace Amasty\GdprCookie\Model\Cookie;

class CookieBackend extends CookieManagement
{
    protected function createCookieCollection(int $storeId = 0)
    {
        $collection = $this->cookieCollectionFactory->create();
        $collection->setStoreId($storeId);

        return $collection;
    }
}
