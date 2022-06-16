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
 * Used in listings to recombine locations option source for filter
 */
class CheckboxLocationListing extends CheckboxLocationCombine
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

            $formattedLocations[] = [
                'label' => $location['label'] ?? '',
                'value' => $this->getElementIdByLabel($location['label'] ?? ''),
                'optgroup' => $this->processLocationValue($location['value']),
            ];
        }

        return $formattedLocations;
    }

    /**
     * Recursively replace child options to optgroups
     */
    private function processLocationValue(array $locationValues): array
    {
        return array_map(function ($locationValue) {
            if (isset($locationValue['value']) && is_array($locationValue['value'])) {
                $locationValue['optgroup'] = $this->processLocationValue($locationValue['value']);
                $locationValue['value'] = $this->getElementIdByLabel($locationValue['label'] ?? '');
            }

            return $locationValue;
        }, $locationValues);
    }

    private function getElementIdByLabel($label): string
    {
        if (!$label) {
            return uniqid();
        }

        return str_replace(' ', '_', strtolower((string)$label));
    }
}
