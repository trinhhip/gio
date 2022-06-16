<?php

namespace Omnyfy\Mcm\Block\Adminhtml\Sales\Order\Creditmemo;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $_creditmemo = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @var \Omnyfy\Mcm\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Omnyfy\Mcm\Helper\Data $helper
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Omnyfy\Mcm\Helper\Data $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\DataObject
     */

    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }

    public function initTotals()
    {
        $this->getParentBlock();

        if ($this->getMcmTransactionFee() > 0) {
            $mcmTransactionFee = $this->dataObjectFactory->create()->setData(
                [
                    'code' => 'mcm_transaction_fee',
                    'value' => $this->getMcmTransactionFee(),
                    'label' => 'Transaction Fee (incl. tax)',
                ]
            );

            $this->getParentBlock()->addTotalBefore($mcmTransactionFee, 'grand_total');
        }

        return $this;
    }

    public function getMcmTransactionFee()
    {
        $creditMemo = $this->getCreditmemo();
        return $creditMemo->getGrandTotal() - $creditMemo->getAdjustment() - $creditMemo->getSubtotal()- $creditMemo->getTaxAmount() -$creditMemo->getShippingAmount() -$creditMemo->getDiscountAmount();
    }
}
