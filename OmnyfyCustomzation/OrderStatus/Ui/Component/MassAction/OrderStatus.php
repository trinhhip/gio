<?php


namespace OmnyfyCustomzation\OrderStatus\Ui\Component\MassAction;

use Magento\Framework\UrlInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Zend\Stdlib\JsonSerializable;

class OrderStatus implements JsonSerializable
{


    /**
     * Additional options params
     *
     * @var array
     */
    protected $data;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Sub-actions Base URL
     *
     * @var string
     */
    protected $urlPath;

    /**
     * Sub-actions additional params
     *
     * @var array
     */
    protected $additionalData = [];

    /**
     * Options
     *
     * @var array
     */
    protected $options = [];
    /**
     * @var CollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * @param UrlInterface $urlBuilder
     * @param CollectionFactory $statusCollectionFactory
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        CollectionFactory $statusCollectionFactory,
        array $data = []
    )
    {
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
        $this->statusCollectionFactory = $statusCollectionFactory;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if (empty($this->options)) {
            $this->prepareOptionsData();
            $this->getMatchingOptions();
            $this->options = array_values($this->options);
        }

        return $this->options;
    }

    /**
     * @return string
     */
    protected function getUrlPath()
    {
        return 'mageworx_ordersgrid/order_grid/massShipInvoiceCapture';
    }

    protected function getMatchingOptions()
    {
        foreach ($this->getStatusOptions() as $statusOption){
            $this->options[$statusOption['value']] = array_merge_recursive(
                $this->options[$statusOption['value']] = [
                    'type' => $statusOption['value'],
                    'label' => __($statusOption['label']),
                    'url' => $this->urlBuilder->getUrl(
                        'omnyfycustom_order/order/masschangestatus',
                        [
                            'status' => $statusOption['value'],
                        ]
                    ),
                    'confirm' => [
                        'title' => __('Capture'),
                        'message' => __('Are you sure you want to change status selected items?')
                    ]
                ],
                $this->additionalData
            );
        }
    }

    /**
     * Prepare sub-actions addition data
     *
     * @return void
     */
    protected function prepareOptionsData()
    {
        $this->urlPath = $this->getUrlPath();

        foreach ($this->data as $dataKey => $dataValue) {
            $this->additionalData[$dataKey] = $dataValue;
        }
    }

    /**
     * Get status options
     *
     * @return array
     */
    public function getStatusOptions()
    {
        return $this->statusCollectionFactory->create()->toOptionArray();
    }
}