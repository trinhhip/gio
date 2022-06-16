<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\ActionLogRepositoryInterface;
use Magento\Customer\Model\Session;

class ActionLogger
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Visitor
     */
    private $visitor;

    /**
     * @var ActionLogFactory
     */
    private $actionLogFactory;

    /**
     * @var ActionLogRepositoryInterface
     */
    private $repository;

    public function __construct(
        Session $customerSession,
        Visitor $visitor,
        ActionLogFactory $actionLogFactory,
        ActionLogRepositoryInterface $repository
    ) {
        $this->customerSession = $customerSession;
        $this->visitor = $visitor;
        $this->actionLogFactory = $actionLogFactory;
        $this->repository = $repository;
    }

    public function logAction($action, $customerId = null, $comment = null)
    {
        if (!$customerId) {
            $customerId = $this->customerSession->getId();
        }

        /** @var ActionLog $actionLog */
        $actionLog = $this->actionLogFactory->create();

        $actionLog
            ->setCustomerId($customerId)
            ->setIp($this->visitor->getRemoteIp())
            ->setAction($action)
            ->setComment((string)$comment);

        $this->repository->save($actionLog);
    }
}
