<?php

namespace Omnyfy\Mcm\Ui\Component\Listing\Column\PayoutOrder;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;

/**
 * Class CategoryFee
 * @package Omnyfy\Mcm\Ui\Component\Listing\Column\PayoutOrder
 */
class CategoryFee extends Column{

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;
    /**
     * @var
     */
    protected $config;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * CategoryFee constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param VendorRepositoryInterface $vendorRepository
     * @param \Omnyfy\Mcm\Model\Config $config
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        VendorRepositoryInterface $vendorRepository,
        \Omnyfy\Mcm\Model\Config $config,
        \Magento\Framework\App\Request\Http $request,
        array $components = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->vendorRepository = $vendorRepository;
        if ($this->isWholeSaleVendor()) {
            $data = [];
        }
        parent::__construct($context, $uiComponentFactory,$components, $data);
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

    /**
     * @return bool
     */
    public function isWholeSaleVendor(){
        $vendorId = $this->request->getParam('vendor_id');
        if ($this->getVendorPayoutBasisType($vendorId) == PayoutBasisType::WHOLESALE_VENDOR_VALUE) {
            return true;
        }
        return false;
    }

}