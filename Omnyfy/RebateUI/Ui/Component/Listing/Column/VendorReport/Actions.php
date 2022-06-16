<?php

namespace Omnyfy\RebateUI\Ui\Component\Listing\Column\VendorReport;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Actions
 * @package Omnyfy\RebateUI\Ui\Component\Listing\Column
 */
class Actions extends Column
{
    /**
     *
     */
    const URL_PATH_VIEW = 'sales/order/view';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    protected $request;

    protected $backendSession;

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
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\App\RequestInterface $request,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->backendSession = $backendSession;
        $this->request = $request;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function getVendorId() {
        $vendorId = '';
        $vendorId = $this->request->getParam('vendor_id');
        if (!$vendorId) {
            $vendorInfo = $this->backendSession->getVendorInfo();
            if (!empty($vendorInfo) && isset($vendorInfo['vendor_id'])) {
                $vendorId = $vendorInfo['vendor_id'];
            }
        }
        return $vendorId;
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
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_VIEW,
                                ['order_id' => $item['order_id']]
                            ),
                            'label' => __('View')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
