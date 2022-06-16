<?php
namespace Omnyfy\Vendor\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;


class SourceActions extends \Magento\Ui\Component\Listing\Columns\Column
{

    const SOURCE_EDIT_PATCH = 'inventory/source/edit';
    const SOURCE_VIEW_PRODUCT_PATCH = 'omnyfy_vendor/source/inventory';

    protected $urlBuilder;

    /**
    * @param ContextInterface $context
    * @param UiComponentFactory $uiComponentFactory
    * @param UrlInterface $urlBuilder
    * @param array $components
    * @param array $data
    * @param string $editUrl
    */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
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
                $name = $this->getData('name');
                if (isset($item['source_code'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::SOURCE_EDIT_PATCH, ['source_code' => $item['source_code']]),
                        'label' => __('Edit')
                    ];

                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::SOURCE_VIEW_PRODUCT_PATCH, ['source_code' => $item['source_code']]),
                        'label' => __('View Inventory')
                    ];
                }
            }
        }
        return $dataSource;
    }
}
