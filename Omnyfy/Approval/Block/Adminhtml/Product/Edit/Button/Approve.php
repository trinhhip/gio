<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-22
 * Time: 10:06
 */
namespace Omnyfy\Approval\Block\Adminhtml\Product\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Omnyfy\Core\Block\Adminhtml\Button;

class Approve extends Button implements ButtonProviderInterface
{
    protected $helper;

    protected $approvalHelper;

    protected $session;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Omnyfy\Vendor\Helper\Data $helper,
        \Omnyfy\Approval\Helper\Data $approvalHelper,
        \Magento\Backend\Model\Session $session
    ) {
        $this->helper = $helper;
        $this->approvalHelper = $approvalHelper;
        $this->session = $session;
        parent::__construct($context, $registry);
    }

    public function getButtonData()
    {
        $product = $this->registry->registry('current_product');
        if (empty($product) || empty($product->getId())) {
            return [];
        }

        $vendorInfo = $this->session->getVendorInfo();
        if (!empty($vendorInfo)) {
            return [];
        }

        if ($this->helper->isMoProduct($product->getId())) {
            return [];
        }

        $record = $this->approvalHelper->getRecordByProductId($product->getId());
        if (!empty($record) && $record->getStatus() != \Omnyfy\Approval\Model\Source\Status::STATUS_IN_PUBLISHING
            && $record->getStatus() != \Omnyfy\Approval\Model\Source\Status::STATUS_SUBMITTED_TO_REVIEW) {
            return [];
        }

        $params = ['product' => $product->getId()];
        return [
            'label' => __('Approve'),
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('omnyfy_approval/record/approve', $params)),
            'class' => 'primary',
            'sort_order' => 25
        ];
    }
}
