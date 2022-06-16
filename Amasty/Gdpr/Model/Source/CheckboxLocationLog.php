<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */

declare(strict_types=1);

namespace Amasty\Gdpr\Model\Source;

use Amasty\Gdpr\Model\ConsentLogger;

/**
 * Adding more locations for display in log listing
 */
class CheckboxLocationLog extends CheckboxLocationCombine
{
    public function toOptionArray()
    {
        $locations = parent::toOptionArray();
        $formattedLocations[] = [
            'value' => ConsentLogger::FROM_PRIVACY_SETTINGS,
            'label' => __('Optional Consent at Account Privacy Settings')
        ];
        $formattedLocations[] = [
            'value' => ConsentLogger::PRIVACY_POLICY_POPUP,
            'label' => __('Privacy Policy Popup')
        ];

        foreach ($locations as $location) {
            if (!is_array($location['value'])) {
                $formattedLocations[] = $location;
                continue;
            }

            foreach ($location['value'] as $combinedLocation) {
                $formattedLocations[] = $combinedLocation;
            }
        }

        return $formattedLocations;
    }
}
