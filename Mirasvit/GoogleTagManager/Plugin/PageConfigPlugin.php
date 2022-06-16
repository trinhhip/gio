<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-gtm
 * @version   1.0.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\GoogleTagManager\Plugin;

use Magento\Framework\View\Page\Config;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\GoogleTagManager\Model\Config as GtmConfig;

/**
 * @see \Magento\Framework\View\Page\Config::getIncludes()
 */
class PageConfigPlugin
{
    private $config;

    private $storeManager;

    public function __construct(
        GtmConfig $config,
        StoreManagerInterface $storeManager
    ) {
        $this->config       = $config;
        $this->storeManager = $storeManager;
    }

    public function afterGetIncludes(Config $subject, ?string $result): ?string
    {
        $store = $this->storeManager->getStore();

        if ($this->config->getGeneralIsEnable($store)) {
            $regularCode = $this->config->getGeneralRegularCode($store);

            $result .= $regularCode;
        }

        return $result;
    }
}
