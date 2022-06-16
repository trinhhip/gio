<?php


namespace OmnyfyCustomzation\B2C\Plugin\Wishlist;


use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\Index\Send;

class Shared
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
    }

    public function afterExecute(Send $subject, $result)
    {
        if ($result instanceof Redirect) {
           $this->messageManager->getMessages(true);
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('buyer/wishlist/shared');
            return $resultRedirect;
        }
        return $result;
    }
}