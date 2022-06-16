<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */

declare(strict_types=1);

namespace Amasty\Gdpr\Observer\Checkout;

use Amasty\Gdpr\Model\FlagRegistry;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IndexAction implements ObserverInterface
{
    /**
     * @var FlagRegistry
     */
    private $flagRegistry;

    public function __construct(
        FlagRegistry $flagRegistry
    ) {
        $this->flagRegistry = $flagRegistry;
    }

    public function execute(Observer $observer)
    {
        $this->flagRegistry->setFlagEnableSessionPlugin(true);
    }
}
