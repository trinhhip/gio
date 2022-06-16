<?php
namespace Omnyfy\VendorFeatured\Ui\Component\Listing\Column;

class SpotlightBannerActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    const URL_PATH_EDIT = 'omnyfy_vendorfeatured/spotlightbanner/edit';
    const URL_PATH_ASSIGNVENDORS = 'omnyfy_vendorfeatured/spotlightbanner/assignvendors';
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
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
                if (isset($item['banner_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'banner_id' => $item['banner_id']
                                ]
                            ),
                            'label' => __('Edit Placement')
                        ],
                        'assign_vendors' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_ASSIGNVENDORS,
                                [
                                    'banner_id' => $item['banner_id']
                                ]
                            ),
                            'label' => __('Assign Vendors')
                        ]
                    ];
                }
            }
        }
        
        return $dataSource;
    }
}
