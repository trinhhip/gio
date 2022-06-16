<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


declare(strict_types=1);

namespace Amasty\GdprCookie\Plugin\Framework\View\TemplateEngine;

use Amasty\GdprCookie\ViewModel\TemplateEngine\CookieScript;
use Amasty\GdprCookie\Model\ConfigProvider;
use Magento\Framework\View\Element\BlockInterface;

class PhpPlugin
{
    /**
     * @var CookieScript
     */
    private $cookieScript;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        CookieScript $cookieScript,
        ConfigProvider $configProvider
    ) {
        $this->cookieScript = $cookieScript;
        $this->configProvider = $configProvider;
    }

    public function beforeRender(
        \Magento\Framework\View\TemplateEngine\Php $subject,
        BlockInterface $block,
        $fileName,
        array $dictionary = []
    ) {
        if (!isset($dictionary['amCookieScript']) && $this->configProvider->isCookieBarEnabled()) {
            $dictionary['amCookieScript'] = $this->cookieScript;
        }

        return [$block, $fileName, $dictionary];
    }
}
