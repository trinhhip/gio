<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product\Filter\GlobalResolver;

use Amasty\AltTagGenerator\Model\Template\Product\Filter\GlobalResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class WebsiteResolver implements GlobalResolverInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function execute(): string
    {
        return $this->storeManager->getWebsite()->getName();
    }
}
