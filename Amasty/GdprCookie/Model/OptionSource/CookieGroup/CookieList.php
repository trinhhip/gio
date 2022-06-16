<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


declare(strict_types=1);

namespace Amasty\GdprCookie\Model\OptionSource\CookieGroup;

use Amasty\GdprCookie\Model\Cookie\CookieBackend;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;

class CookieList implements OptionSourceInterface
{
    /**
     * @var CookieBackend
     */
    private $cookieBackend;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        CookieBackend $cookieBackend,
        RequestInterface $request
    ) {
        $this->cookieBackend = $cookieBackend;
        $this->request = $request;
    }

    public function toOptionArray()
    {
        return array_map(function ($cookie) {
            return [
                'value' => $cookie->getId(),
                'label' => $cookie->getName(),
            ];
        }, $this->toArray());
    }

    public function toArray(): array
    {
        $storeId = (int)$this->request->getParam('store');

        return $this->cookieBackend->getCookies($storeId);
    }
}
