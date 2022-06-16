<?php
namespace Omnyfy\RebateUI\Ui\Component\Listing\Column\Table;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Omnyfy\RebateCore\Helper\Calculation;
use Magento\Framework\App\RequestInterface;
use Omnyfy\RebateUI\Helper\Data;

/**
 * Class Actions
 * @package Omnyfy\RebateUI\Ui\Component\Listing\Column
 */
class LastRebateMonth extends Column
{
    /**
     * @var calculation
     */
    protected $calculation;

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
        Calculation $calculation,
        RequestInterface $request,
        UiComponentFactory $uiComponentFactory,
        Data $helper,
        array $components = [],
        array $data = []
    )
    {
        $this->calculation = $calculation;
        $this->request = $request;
        $this->helper = $helper;
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
                    $item['last_rebate_month'] = $item['last_rebate_month'] ? $this->currency($item['last_rebate_month']) : $this->currency(0);
                }
            }
        }
        return $dataSource;
    }

    public function currency($value) {
        return $this->helper->formatToBaseCurrency($value);
    }

}
