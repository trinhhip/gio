<?php

namespace Omnyfy\RebateUI\Ui\Component\Listing\Column\VendorReport;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Omnyfy\RebateUI\Helper\Data;

/**
 * Class Actions
 * @package Omnyfy\RebateUI\Ui\Component\Listing\Column
 */
class OrderStatus extends Column
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var RequestInterface
     */
    protected $request;

    protected $helper;


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
        OrderRepositoryInterface $orderRepository,
        RequestInterface $request,
        UiComponentFactory $uiComponentFactory,
        Data $helper,
        array $components = [],
        array $data = []
    )
    {
        $this->orderRepository = $orderRepository;
        $this->request = $request;
        $this->helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        $vendorId = $this->request->getParam('vendor_id');
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $order = $this->orderRepository->get($item['order_id']);
                    $item['order_status'] = $order->getStatus();
                }
            }
        }

        return $dataSource;
    }

}
