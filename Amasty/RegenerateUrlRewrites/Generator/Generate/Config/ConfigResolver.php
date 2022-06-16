<?php

declare(strict_types = 1);

namespace Amasty\RegenerateUrlRewrites\Generator\Generate\Config;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface;
use Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterfaceFactory;
use Amasty\RegenerateUrlRewrites\Model\ConfigProvider;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class ConfigResolver
{
    const ENTITY_TYPE_CATEGORY = 1;
    const ENTITY_TYPE_PRODUCT = 2;
    const VALID_ENTITY_TYPES = ['category', 'product'];

    /**
     * @var GenerateConfigInterfaceFactory
     */
    private $generateConfigFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        GenerateConfigInterfaceFactory $generateConfigFactory,
        ConfigProvider $configProvider
    ) {
        $this->generateConfigFactory = $generateConfigFactory;
        $this->configProvider = $configProvider;
    }

    /**
     * @param string $entityType
     * @param int|null $storeId
     * @return GenerateConfigInterface
     * @throws LocalizedException
     */
    public function fromSettings(string $entityType, ?int $storeId = null): GenerateConfigInterface
    {
        $this->validateEntityType($entityType);
        /** @var GenerateConfigInterface $config */
        $config = $this->generateConfigFactory->create();
        $config->setStoreId($this->configProvider->getStoreViewsForApply($storeId));

        $config->setRegenerateEntityType($entityType);
        $methodType = ucfirst($entityType);

        $config->setIncludeToRegeneration(
            $this->configProvider->{'isInclude' . $methodType . 'Regeneration'}($storeId)
        );

        if (!$this->configProvider->{'isUse' . $methodType . 'RangeRegenerate'}($storeId)
            || !($idsRange = $this->configProvider->{'get' . $methodType . 'IdRangeRegenerate'}($storeId))
        ) {
            $idsRange = null;
        }
        $config->setIdsRange($idsRange);

        if (!$this->configProvider->{'isUse' . $methodType . 'IdsRegenerate'}($storeId)
            || !($specificIds = $this->configProvider->{'get' . $methodType . 'IdsRegenerate'}($storeId))
        ) {
            $specificIds = null;
        }
        $config->setSpecificIds($specificIds);

        $config->setNoReindex($this->configProvider->isSkipReindex($storeId));

        $isSkipCacheFlash = $this->configProvider->isSkipCacheFlash($storeId);
        $config->setNoCacheFlush($isSkipCacheFlash);
        $config->setNoCacheClean($isSkipCacheFlash);

        return $config;
    }

    /**
     * @param string $entityType
     * @param array $formData
     * @return GenerateConfigInterface
     * @throws LocalizedException
     */
    public function fromForm(string $entityType, array $formData): GenerateConfigInterface
    {
        $this->validateEntityType($entityType);
        if (empty($formData['groups'])) {
            throw new LocalizedException(__('Invalid form data'));
        }

        /** @var GenerateConfigInterface $config */
        $config = $this->generateConfigFactory->create();
        $data = new DataObject($formData['groups']);
        $storeView = $data->getData('url_rewrites_regeneration/fields/apply_store_views/value');
        $storeView = $storeView ? (string)$storeView : null;
        $config->setStoreId($storeView);

        $group = 'url_rewrites_' . $entityType . '/fields/';
        $config->setRegenerateEntityType($entityType);

        $config->setIncludeToRegeneration(
            (bool)$data->getData(
                $group . 'include_' . $entityType . '_regeneration/value'
            )
        );

        if (!$data->getData($group . 'use_' . $entityType . '_range_regenerate/value')
            || !($idsRange = (string)$data->getData($group . $entityType . '_id_range_regenerate/value'))
        ) {
            $idsRange = null;
        }
        $config->setIdsRange($idsRange);

        if (!$data->getData($group . 'use_' . $entityType . '_ids_regenerate/value')
            || !($specificIds = (string)$data->getData($group . $entityType . '_ids_regenerate/value'))
        ) {
            $specificIds = null;
        }
        $config->setSpecificIds($specificIds);

        $config->setNoReindex((bool)$data->getData('general/fields/skip_reindex/value'));

        $isSkipCacheFlash = (bool)$data->getData('general/fields/skip_cache_flash/value');
        $config->setNoCacheFlush($isSkipCacheFlash);
        $config->setNoCacheClean($isSkipCacheFlash);

        return $config;
    }

    /**
     * @param int $entityTypeId
     * @return string
     * @throws LocalizedException
     */
    public function getEntityType(int $entityTypeId): string
    {
        switch ($entityTypeId) {
            case self::ENTITY_TYPE_CATEGORY:
                $entityType = 'category';
                break;
            case self::ENTITY_TYPE_PRODUCT:
                $entityType = 'product';
                break;
            default:
                throw new LocalizedException(__('Invalid entity type'));
        }

        return $entityType;
    }

    /**
     * @param string $entityType
     * @throws LocalizedException
     */
    private function validateEntityType(string $entityType): void
    {
        if (!in_array($entityType, self::VALID_ENTITY_TYPES)) {
            throw new LocalizedException(__('Invalid entity type'));
        }
    }
}
