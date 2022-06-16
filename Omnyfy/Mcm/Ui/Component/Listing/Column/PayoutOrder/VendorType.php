<?php

namespace Omnyfy\Mcm\Ui\Component\Listing\Column\PayoutOrder;

use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType as PayoutBasisTypeOptions;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;

/**
 * Class VendorType
 * @package Omnyfy\Vendor\Ui\Component\Listing\Column
 */
class VendorType extends Column
{
    /**
     * @var PayoutBasisTypeOptions
     */
    protected $payoutBasisType;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

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
        \Magento\Framework\App\Request\Http $request,
        VendorRepositoryInterface $vendorRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->payoutBasisType = $payoutBasisType;
        $this->request = $request;
        $this->vendorRepository = $vendorRepository;
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
                $item['payout_basis_type'] = $this->getLabelPayoutBasisType($this->getVendorPayoutBasisType());
            }
        }
        return $dataSource;
    }

    public function getOptionPayoutBasisType() {
        return $this->payoutBasisType->toOptionArray();
    }

    public function getLabelPayoutBasisType($value){
        $options = $this->getOptionPayoutBasisType();
        if ($value != NULL) {
            foreach ($options as $option) {
                if ($option['value'] == $value) {
                    return $option['label'];
                }
            }
        }
        return NULL;
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getVendorPayoutBasisType(){
        $vendorId = $this->request->getParam('vendor_id');
        if ($vendorId) {
            $vendor = $this->vendorRepository->getById($vendorId);
            return $vendor->getPayoutBasisType();
        }
        return false;
    }

}