<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Setup;

use Magento\Framework\DB\AggregatedFieldDataConverterFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ProductMetadataInterface;
use Amasty\Groupcat\Api\Data\RuleInterface;
use Magento\Framework\DB\FieldToConvert;

class RecurringData implements InstallDataInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AggregatedFieldDataConverterFactory
     */
    private $converterFactory;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        MetadataPool $metadataPool,
        ProductMetadataInterface $productMetadata,
        AggregatedFieldDataConverterFactory $converterFactory
    ) {
        $this->productMetadata = $productMetadata;
        $this->metadataPool = $metadataPool;
        $this->converterFactory = $converterFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2', '>=')) {
            $this->convertSerializedDataToJson($setup);
        }
    }

    /**
     * Convert metadata from serialized to JSON format:
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     */
    public function convertSerializedDataToJson($setup)
    {
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);

        /** @var \Magento\Framework\DB\AggregatedFieldDataConverter $aggregatedFieldConverter */
        $aggregatedFieldConverter = $this->converterFactory->create();
        $aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    \Magento\Framework\DB\DataConverter\SerializedToJson::class,
                    $setup->getTable('amasty_groupcat_rule'),
                    $metadata->getLinkField(),
                    'conditions_serialized'
                ),
                new FieldToConvert(
                    \Magento\Framework\DB\DataConverter\SerializedToJson::class,
                    $setup->getTable('amasty_groupcat_rule'),
                    $metadata->getLinkField(),
                    'actions_serialized'
                ),
            ],
            $setup->getConnection()
        );
    }
}
