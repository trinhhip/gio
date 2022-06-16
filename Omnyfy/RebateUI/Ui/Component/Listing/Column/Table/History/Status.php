<?php

namespace Omnyfy\RebateUI\Ui\Component\Listing\Column\Table\History;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Omnyfy\RebateCore\Ui\Form\StatusInvoiceRebate as OptionStatusInvoiceRebate;

/**
 * Class Actions
 * @package Omnyfy\RebateUI\Ui\Component\Listing\Column\Table\History
 */
class Status extends Column
{

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var OptionStatusInvoiceRebate
     */
    protected $optionStatusInvoiceRebate;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        OptionStatusInvoiceRebate $optionStatusInvoiceRebate,
        array $data = []
    )
    {
        $this->optionStatusInvoiceRebate = $optionStatusInvoiceRebate;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $arrStatusInvoice = $this->optionStatusInvoiceRebate->toArray();
            foreach ($dataSource['data']['items'] as &$items) {
                if (!empty($arrStatusInvoice[$items['status']])) {
                    $items['status'] = $arrStatusInvoice[$items['status']]->getText();
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
