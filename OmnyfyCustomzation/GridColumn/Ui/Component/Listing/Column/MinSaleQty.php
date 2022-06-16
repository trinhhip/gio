<?php


namespace OmnyfyCustomzation\GridColumn\Ui\Component\Listing\Column;

use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class MinSaleQty extends Column
{
    /**
     * @var StockItemRepository
     */
    protected $stockItemRepository;


    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StockItemRepository $stockItemRepository,
        array $components = [],
        array $data = []
    )
    {
        $this->stockItemRepository = $stockItemRepository;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    public function prepareDataSource(array $dataSource)
    {
        $fieldName = $this->getData('name');
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $productId = $item['entity_id'];
                try {
                    $stock = $this->stockItemRepository->get($productId);
                    $minSaleQty = $stock->getMinSaleQty();
                } catch (NoSuchEntityException $e) {
                    $minSaleQty = '';
                }
                $item[$fieldName] = $minSaleQty;
            }
        }
        return $dataSource;
    }
}
