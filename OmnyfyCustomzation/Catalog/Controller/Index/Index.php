<?php

namespace OmnyfyCustomzation\Catalog\Controller\Index;

use Magento\Framework\App\Action\Context;

/**
 * Class Index
 *
 * @package OmnyfyCustomzation\Catalog\Controller\Index
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $coreSession;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $sessionManager
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $sessionManager
    )
    {
        $this->coreSession = $sessionManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $sessionData = $this->coreSession->getShowPopup();
        $this->coreSession->setShowPopup(false);
        return $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData([
                'success' => true,
                'showPopup' => $sessionData
            ]);
    }
}