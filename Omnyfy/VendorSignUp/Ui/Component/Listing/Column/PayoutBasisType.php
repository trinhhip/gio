<?php

namespace Omnyfy\VendorSignUp\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType as BasisType;


class PayoutBasisType extends Column
{

    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

	public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['payout_basis_type'])) {
                    if ($item['payout_basis_type'] == BasisType::WHOLESALE_VENDOR_VALUE) {
                        $item['payout_basis_type'] = "Wholesale Vendor";
                    } elseif ($item['payout_basis_type'] == BasisType::COMMISSION_VENDOR_VALUE) {
                        $item['payout_basis_type'] = "Commission Vendor";
                    }
                }
            }
        }

        return $dataSource;
    }
}
