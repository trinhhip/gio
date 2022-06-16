<?php

namespace Omnyfy\Rma\Block\Adminhtml\Rma;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Mirasvit\Rma\Helper\Controller\Rma\Grid as GridHelper;
use Mirasvit\Rma\Helper\StringHelper;
use Mirasvit\Rma\Model\RmaFactory;
use Mirasvit\Rma\Model\StatusFactory;

class Grid extends \Mirasvit\Rma\Block\Adminhtml\Rma\Grid
{
    /**
     * @var GridHelper
     */
    private $gridHelper;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface
     */
    private $rmaOrderService;
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Framework\Escaper $escaper,
        StatusFactory $statusFactory,
        RmaFactory $rmaFactory,
        GridHelper $gridHelper,
        StringHelper $rmaString,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        Context $context,
        BackendHelper $backendHelper,
        array $data = []
    )
    {
        parent::__construct($orderItemRepository, $statusFactory, $rmaFactory, $gridHelper, $rmaString, $rmaSearchManagement, $context, $backendHelper, $data);
        $this->gridHelper = $gridHelper;
        $this->rmaOrderService = $rmaOrderService;
        $this->escaper = $escaper;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rma_grid');
        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->gridHelper->getIncrementId($this);
        $this->getOrderIncrementId($this);
        $this->gridHelper->getUserId($this);
        $this->gridHelper->getLastReplyName($this);
        $this->gridHelper->getStatusId($this);
        $this->gridHelper->getCreatedAt($this);
        $this->gridHelper->getUpdatedAt($this);
        $this->gridHelper->getStoreId($this);
        $this->gridHelper->getItems($this);
        $this->gridHelper->getAction($this);
        $this->gridHelper->getCustomFields($this);

        return \Magento\Backend\Block\Widget\Grid\Extended::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('rma/customer/rma', ['_current' => true]);
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getOrderIncrementId($grid)
    {
        $grid->addColumn('order_id', [
            'header'       => __('Order #'),
            'index'        => 'order_id',
            'filter_index' => 'main_table.order_id',
            'frame_callback' => [$grid, '_setOrderId'],
        ]);
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid    $renderedValue
     * @param \Mirasvit\Rma\Model\Rma                   $rma
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool                                      $isExport
     *
     * @return string
     */
    public function _setOrderId($renderedValue, $rma, $column, $isExport)
    {
        $str = '';
        $orders = $this->rmaOrderService->getOrders($rma);
        if (!count($orders)) {
            $str .= __('Removed Order')->render();
        }
        foreach ($orders as $order) {
            if ($order) {
                $str .= '#';
                if ($order->getIsOffline()) {
                    $str .= $this->escaper->escapeHtml($order->getReceiptNumber());
                } else {
                    $str .= $order->getIncrementId();
                }
                $str .= '<br>';
            } else {
                $str .= __('Removed Order')->render();
            }
        }

        return $str;
    }
}