<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */

declare(strict_types=1);

namespace Amasty\Gdpr\Plugin\Multishipping\Model\Checkout\Type;

use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Observer\Checkout\ConsentRegistry;
use Magento\Framework\App\RequestInterface;

class MultishippingPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ConsentRegistry
     */
    private $consentRegistry;

    public function __construct(
        RequestInterface $request,
        ConsentRegistry $consentRegistry
    ) {
        $this->request = $request;
        $this->consentRegistry = $consentRegistry;
    }

    public function beforeCreateOrders(): void
    {
        if ($consents = (array)$this->request->getParam(RegistryConstants::CONSENTS)) {
            $this->consentRegistry->setConsents($consents);
        }
    }
}
