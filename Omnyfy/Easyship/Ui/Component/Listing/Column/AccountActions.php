<?php
namespace Omnyfy\Easyship\Ui\Component\Listing\Column;

class AccountActions extends \Magento\Ui\Component\Listing\Columns\Column
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
            $title = $item['name'];

            $item[$this->getData('name')]['edit'] = [
                'href' => $this->urlBuilder->getUrl(
                    'omnyfy_easyship/account/edit',
                    ['id' => $item['entity_id']]
                ),
                'label' => __('Edit'),
                'hidden' => false,
            ];
            $item[$this->getData('name')]['delete'] = [
                'href' => $this->urlBuilder->getUrl(
                    'omnyfy_easyship/account/delete',
                    [
                        'id' => $item['entity_id']
                    ]
                ),
                'label' => __('Delete'),
                'confirm' => [
                    'title' => __('Delete %1', $title),
                    'message' => __('Are you sure you want to delete %1 record?', $title),
                ],
                'post' => true
            ];
        }
        return $dataSource;
    }
}
