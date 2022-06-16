<?php

namespace Omnyfy\RebateUI\Ui\Component\Listing\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Omnyfy\RebateCore\Ui\Form\PaymentFrequency as OptionPaymentFrequency ;

/**
 * Class Actions
 * @package Omnyfy\RebateUI\Ui\Component\Listing\Column
 */
class PaymentFrequency extends Column
{
    /**
     * @var PaymentFrequency
     */
    protected $paymentFrequency;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PaymentFrequency $paymentFrequency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OptionPaymentFrequency $paymentFrequency,
        array $components = [],
        array $data = []
    )
    {
        $this->paymentFrequency = $paymentFrequency;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $arrPaymentFrequency = $this->paymentFrequency->toArray();
            foreach ($dataSource['data']['items'] as &$items) {
                if (!empty($arrPaymentFrequency[$items['payment_frequency']])) {
                    $items['payment_frequency'] = $arrPaymentFrequency[$items['payment_frequency']]->getText();
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
