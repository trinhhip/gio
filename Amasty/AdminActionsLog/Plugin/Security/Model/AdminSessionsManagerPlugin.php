<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Plugin\Security\Model;

use Amasty\AdminActionsLog\Api\ActiveSessionRepositoryInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Exception\NoSuchEntityException;

class AdminSessionsManagerPlugin
{
    /**
     * @var ActiveSessionRepositoryInterface
     */
    private $activeSessionRepository;

    /**
     * @var Session
     */
    private $authSession;

    public function __construct(
        ActiveSessionRepositoryInterface $activeSessionRepository,
        Session $authSession
    ) {
        $this->activeSessionRepository = $activeSessionRepository;
        $this->authSession = $authSession;
    }

    public function afterProcessLogin()
    {
        $sessionId = $this->authSession->getSessionId();
        $adminSessionInfoId = $this->authSession->getAdminSessionInfoId();

        if (!empty($sessionId) && $adminSessionInfoId) {
            try {
                $activeSessionModel = $this->activeSessionRepository->getBySessionId($sessionId);
            } catch (NoSuchEntityException $exception) {
                return;
            }

            $activeSessionModel->setAdminSessionInfoId((int)$adminSessionInfoId);
            $this->activeSessionRepository->save($activeSessionModel);
        }
    }
}
