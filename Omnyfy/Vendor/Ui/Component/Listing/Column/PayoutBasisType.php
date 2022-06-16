<?php

namespace Omnyfy\Vendor\Ui\Component\Listing\Column;

use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType as PayoutBasisTypeOptions;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class PayoutBasisType
 * @package Omnyfy\Vendor\Ui\Component\Listing\Column
 */
class PayoutBasisType extends Column
{
    /**
     * @var PayoutBasisTypeOptions
     */
    protected $payoutBasisType;

    /**
     * Telephone constructor.
     * 
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PayoutBasisTypeOptions $payoutBasisType,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->payoutBasisType = $payoutBasisType;
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
                if (isset($item['entity_id'])) {
                    $item['payout_basis_type'] = $this->getLabelPayoutBasisType((int)$item['payout_basis_type']);
                }
            }
        }
        return $dataSource;
    }

    public function getOptionPayoutBasisType() {
        return $this->payoutBasisType->toOptionArray();
    }

    public function getLabelPayoutBasisType($value){
        $options = $this->getOptionPayoutBasisType();
        if ($value !== NULL) {
            foreach ($options as $option) {
                if ($option['value'] == $value) {
                    return $option['label'];
                }
            }
        }
        return NULL;
    }

}
