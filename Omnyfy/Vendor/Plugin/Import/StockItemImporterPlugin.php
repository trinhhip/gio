<?php
namespace Omnyfy\Vendor\Plugin\Import;

use Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class StockItemImporterPlugin
{
    /**
     * Stock Item Resource Factory
     *
     * @var ItemFactory $stockResourceItemFactory
     */
    private $stockResourceItemFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * StockItemImporter constructor
     *
     * @param ItemFactory $stockResourceItemFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ItemFactory $stockResourceItemFactory,
        LoggerInterface $logger
    ) {
        $this->stockResourceItemFactory = $stockResourceItemFactory;
        $this->logger = $logger;
    }
    public function aroundImport($subject, callable $proceed, array $stockData) {
        $stockItemResource = $this->stockResourceItemFactory->create();
        $entityTable = $stockItemResource->getMainTable();
        try {
            $stockImportData = array_map(
                function ($stockItemData) {
                    unset($stockItemData['sku']);
                    unset($stockItemData['source_code']);
                    return $stockItemData;
                },
                array_values($stockData)
            );
            $stockItemResource->getConnection()->insertOnDuplicate($entityTable, $stockImportData);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Invalid Stock data for insert'), $e);
        }
    }
}
