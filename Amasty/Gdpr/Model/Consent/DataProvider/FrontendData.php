<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\Consent\DataProvider;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class FrontendData extends AbstractDataProvider
{
    /**
     * @param string $location
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getData(string $location)
    {
        if (!$this->config->isModuleEnabled()) {
            return [];
        }

        return $this->getConsentCollection($location);
    }
}
