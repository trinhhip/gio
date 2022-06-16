<?php

namespace Omnyfy\RebateUI\Ui\Component\Listing\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class VendorReportActions
 * @package Omnyfy\RebateUI\Ui\Component\Listing\Column
 */
class VendorReportActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_HISTORY = 'rebate_ui/table/history';

    /**
     * Url path
     */
    const URL_PATH_VIEW = 'rebate_ui/vendorRebateReport/index';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_VIEW,
                                [
                                    'vendor_id' => $item['entity_id']
                                ]
                            ),
                            'label' => __('View Rebate Report')
                        ],
                        'history' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_HISTORY,
                                [
                                    'vendor_id' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Invoice History')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get instance of escaper
     *
     * @return Escaper
     * @deprecated 101.0.7
     */
    private function getEscaper()
    {
        if (!$this->escaper) {
            $this->escaper = ObjectManager::getInstance()->get(Escaper::class);
        }
        return $this->escaper;
    }
}
