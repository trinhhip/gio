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
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.22
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SearchAutocomplete\InstantProvider;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\Renderer\Translate;
use Magento\Framework\Url;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;

class EmulatorService
{
    private $storeManager;

    private $urlBuilder;

    public function __construct(
        StoreManagerInterface $storeManager,
        Url $urlBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->urlBuilder   = $urlBuilder;
    }


    public function getStoreText(string $sampleText, int $storeId, array $params = []): string
    {
        Phrase::setRenderer(ObjectManager::getInstance()->get(Translate::class));

        $result = (string)__($sampleText, $params);

        $emulation = ObjectManager::getInstance()->get(\Magento\Store\Model\App\Emulation::class);

        try {
            $emulation->startEnvironmentEmulation($storeId, 'frontend', true);

            $state = ObjectManager::getInstance()->get(\Magento\Framework\App\State::class);
            $state->emulateAreaCode('frontend', function (&$result, $sampleText, $parameter) {
                $result = (string)__($sampleText, $parameter);
            }, [&$result, $sampleText, $params]);
        } catch (\Exception $e) {
        } finally {
            $emulation->stopEnvironmentEmulation();
        }

        return $result;
    }

    public function getStoreUrl(int $storeId): string
    {
        $store         = $this->storeManager->getStore($storeId);
        $storeCode     = $store->getCode();
        $baseUrl       = $this->storeManager->getStore($store->getId())->getBaseUrl();
        $allResultsUrl = $this->urlBuilder->getUrl(
            'catalogsearch/result',
            [
                '_query'  => [QueryFactory::QUERY_VAR_NAME => ''],
                '_secure' => false,
                '_scope'  => $store->getId(),
            ]
        );

        if (strrpos($allResultsUrl, $baseUrl) === false && strrpos($baseUrl, '/' . $storeCode . '/') !== false) {
            $baseUrl            = rtrim($baseUrl, '/');
            $allResultsUrlArray = explode('/', $baseUrl) + explode('/', $allResultsUrl);
            $allResultsUrl      = implode('/', $allResultsUrlArray);
        }

        return $allResultsUrl;
    }
}
