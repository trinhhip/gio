<?php
namespace Omnyfy\Easyship\Ui\Component\Listing\Column;

class PickupActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')]['edit'] = [
                'href' => $this->urlBuilder->getUrl(
                    'omnyfy_easyship/bookpickup/view',
                    [
                        'courier' => $item['courier_id'],
                        'source_stock_id' => $item['source_stock_id']
                    ]
                ),
                'label' => __('View Pickup Details'),
                'hidden' => false,
            ];
        }
        return $dataSource;
    }
}