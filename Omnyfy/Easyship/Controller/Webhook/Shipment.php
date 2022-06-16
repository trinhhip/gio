<?php
namespace Omnyfy\Easyship\Controller\Webhook;

class Shipment extends \Magento\Framework\App\Action\Action
{
    protected $webhooks;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Omnyfy\Easyship\Helper\Webhooks $webhooks,
        \Magento\Framework\App\Request\Http $request
    )
    {
        parent::__construct($context);
        $this->webhooks = $webhooks;
        $this->request = $request;
    }

    public function execute()
    {
        $this->webhooks->dispatchEvent($this->request->getParam('account'));
    }
}
