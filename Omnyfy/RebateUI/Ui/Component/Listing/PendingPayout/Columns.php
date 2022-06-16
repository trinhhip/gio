<?php

namespace Omnyfy\RebateUI\Ui\Component\Listing\PendingPayout;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Omnyfy\RebateCore\Api\Data\IVendorRebateRepository;
use Omnyfy\RebateCore\Api\Data\IInvoiceRebateCalculateRepository;
use Omnyfy\RebateCore\Helper\Data as CoreHelper;
use Omnyfy\RebateUI\Helper\Data;
use Omnyfy\RebateCore\Helper\Calculation as CalculationHelper;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    const PREFIX_REBATE =  "rebate_vendor_id_";
    /** @var \Magento\Framework\View\Element\UiComponentFactory */
    protected $componentFactory;

    /** @var int */
    protected $vendorRebateRepository;

    protected $request;

    protected $backendSession;

    protected $helper;

    protected $coreHelper;

    protected $calculationHelper;

    protected $invoiceRebateRepository;

    /**
     * @param ContextInterface $context
     * @param ColumnFactory $columnFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        IVendorRebateRepository $vendorRebateRepository,
        \Magento\Backend\Model\Session $backendSession,
        Data $helper,
        CoreHelper $coreHelper,
        IInvoiceRebateCalculateRepository $invoiceRebateRepository,
        CalculationHelper $calculationHelper,
        array $components = [],
        \Magento\Framework\View\Element\UiComponentFactory $componentFactory,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->vendorRebateRepository = $vendorRebateRepository;
        $this->coreHelper = $coreHelper;
        $this->componentFactory = $componentFactory;
        $this->backendSession = $backendSession;
        $this->helper = $helper;
        $this->request = $request;
        $this->calculationHelper = $calculationHelper;
        $this->invoiceRebateRepository = $invoiceRebateRepository;
    }

    public function getVendorId() {
        $vendorId = '';
        $vendorId = $this->request->getParam('vendor_id');
        if (!$vendorId) {
            $vendorInfo = $this->backendSession->getVendorInfo();
            if (!empty($vendorInfo) && isset($vendorInfo['vendor_id'])) {
                $vendorId = $vendorInfo['vendor_id'];
            }
        }
        return $vendorId;
    }

    /**
     * @return array
     */
    protected function getListRebate()
    {
        $vendorId = $this->getVendorId();
        return $this->vendorRebateRepository->getRebateByVendorActive($vendorId);
    }

   /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($this->coreHelper->isEnable()) {
            $rebates = $this->getListRebate();
            if (isset($dataSource['data']['items'])) {
                foreach ($dataSource['data']['items'] as & $item) {
                    if (isset($item['order_id'])) {
                        foreach ($rebates as $rebate) {
                            $invoiceRebate = $this->invoiceRebateRepository->getInvoiceRebateCalculatesIdByOrderAndVendor($item['order_id'], $this->getVendorId());
                            $item[$this->getNameColumn($rebate->getId())] = $this->getHtmlRebateGird($rebate->getId(), $invoiceRebate->getId());
                        }
                    }
                }
            }
        }

        return $dataSource;
    }

    public function getHtmlRebateGird($reabteId, $rebateVendorInvoiceId){
        $html = "<div class='rebate-total'>".$this->getsumTotalRebateVendorAndInvoice($reabteId, $rebateVendorInvoiceId)."</div>";
        return $html;
    }

    public function getsumTotalRebateVendorAndInvoice($reabteId, $rebateVendorInvoiceId)
    {
        $total = $this->calculationHelper->sumTotalRebateVendorAndInvoice($reabteId, $rebateVendorInvoiceId);
        return  $this->helper->formatToBaseCurrency($total);
    }
    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->columnSortOrder = $this->getDefaultSortOrder();
        if ($this->coreHelper->isEnable()) {
            $addColumnSortOrder = 70;
            foreach ($this->getListRebate() as $rebate) {
                $this->addColumn($rebate, $addColumnSortOrder);
                $addColumnSortOrder++;
            }
        }


        $this->updateActionColumnSortOrder();

        parent::prepare();
    }

    /**
     * @return int
     */
    protected function getDefaultSortOrder()
    {
        $max = 0;
        foreach ($this->components as $component) {
            $config = $component->getData('config');
            if (isset($config['sortOrder']) && $config['sortOrder'] > $max) {
                $max = $config['sortOrder'];
            }
        }
        return ++$max;
    }

    /**
     * Update actions column sort order
     *
     * @return void
     */
    protected function updateActionColumnSortOrder()
    {
        if (isset($this->components['actions'])) {
            $component = $this->components['actions'];
            $component->setData(
                'config',
                array_merge($component->getData('config'), ['sortOrder' => ++$this->columnSortOrder])
            );
        }
    }

    /**
     * @param array $attributeData
     * @param string $columnName
     * @return void
     */
    public function addColumn($rebate, $addColumnSortOrder)
    {
        // Add column sort order so the rebate columns stay in order
        $config = [
            'label' => __($rebate->getLockName()),
            'bodyTmpl' => 'ui/grid/cells/html',
            'sortOrder' => $addColumnSortOrder
        ];
         $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $this->getContext(),
        ];
        $column = $this->componentFactory->create($this->getNameColumn($rebate->getId()), 'column', $arguments);
        $column->prepare();
        $this->addComponent($this->getNameColumn($rebate->getId()), $column);
    }

    public function getNameColumn($idRebateVendor){
        return $this::PREFIX_REBATE . $idRebateVendor;
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        return isset($this->filterMap[$frontendInput]) ? $this->filterMap[$frontendInput] : $this->filterMap['default'];
    }
}
