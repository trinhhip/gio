<?php
namespace Omnyfy\VendorGallery\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory as SourceCollectionFactory;

/**
 * Class Store
 */
class Source extends Column
{
    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var SourceCollectionFactory
     */
    protected $sourceCollectionFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        SourceCollectionFactory $sourceCollectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        $content = '';

		if (count($item['sources']) > 0 ){
			$sourceCollection = $this->sourceCollectionFactory->create();
			$sourceCollection->addFieldToFilter('source_code', ['eq' => $item['sources']]);
			foreach ($sourceCollection->getItems() as $source) {
				$content .= $this->escaper->escapeHtml($source->getData('name')) . "<br/>";
			}
		}

        return $content;
    }
}
