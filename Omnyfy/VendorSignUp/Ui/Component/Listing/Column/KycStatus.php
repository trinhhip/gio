<?php
namespace Omnyfy\VendorSignUp\Ui\Component\Listing\Column;

class KycStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $timeFactory;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Omnyfy\VendorSignUp\Model\VendorKyc $vendorKyc,
        array $components = [],
        array $data = []
    ){
        $this->vendorKyc = $vendorKyc;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $vendorId = $item["entity_id"];
                $vendorKycModel = $this->vendorKyc;
                $vendorKycModel = $vendorKycModel->load($vendorId, 'vendor_id');
                
                if($vendorKycModel->getKycStatus()) {
                    $item['kyc_status'] = $vendorKycModel->getKycStatus();
                } else {
                    $item['kyc_status'] = "pending";
                }
            }
        } 
        return $dataSource;
    }
}
