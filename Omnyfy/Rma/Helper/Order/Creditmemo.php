<?php
namespace Omnyfy\Rma\Helper\Order;

use Mirasvit\Rma\Model\Resolution;

/**
 * Helper for CreditMome
 */
class Creditmemo extends \Mirasvit\Rma\Helper\Order\Creditmemo
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    private $backendUrlManager;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection
     */
    private $invoiceCollection;
    /**
     * @var \Mirasvit\Rma\Api\Service\Resolution\ResolutionManagementInterface
     */
    private $resolutionManagement;
    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    private $creditmemoModel;
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface
     */
    private $itemListBuilder;

    public function __construct(
        \Magento\Backend\Model\Url $backendUrlManager,
        \Magento\Sales\Model\Order\Creditmemo $creditmemoModel,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoiceCollection,
        \Mirasvit\Rma\Api\Service\Resolution\ResolutionManagementInterface $resolutionManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface $itemListBuilder,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        $this->backendUrlManager    = $backendUrlManager;
        $this->moduleManager        = $context->getModuleManager();
        $this->creditmemoModel      = $creditmemoModel;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->invoiceCollection    = $invoiceCollection;
        $this->resolutionManagement = $resolutionManagement;
        $this->itemListBuilder      = $itemListBuilder;
        parent::__construct($backendUrlManager, $creditmemoModel, $creditmemoRepository, $invoiceCollection, $resolutionManagement, $itemListBuilder, $context);
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma    $rma
     * @param \Magento\Sales\Model\Order|\Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return string
     */
    public function getCreditmemoUrl($rma, $order)
    {
        return $this->backendUrlManager->getUrl(
            'sales/order_creditmemo/start',
            ['order_id' => $order->getId(), 'rma_id' => $rma->getId()]
        );
    }

}
