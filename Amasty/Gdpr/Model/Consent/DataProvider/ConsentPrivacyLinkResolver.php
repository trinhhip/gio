<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\Consent\DataProvider;

use Amasty\Gdpr\Api\Data\ConsentInterface;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Amasty\Gdpr\Model\Source\LinkToPolicy;

class ConsentPrivacyLinkResolver
{
    /**
     * @param ConsentInterface $consent
     *
     * @return string
     */
    public function getPrivacyLink(ConsentInterface $consent)
    {
        $privacyLinkType = $consent->getPrivacyLinkType() ?: ConsentLinkType::PRIVACY_POLICY;

        switch ($privacyLinkType) {
            case ConsentLinkType::CMS_PAGE:
            default:
                return LinkToPolicy::PRIVACY_POLICY;
        }
    }
}
