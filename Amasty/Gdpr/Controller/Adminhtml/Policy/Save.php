<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\Policy;

use Amasty\Gdpr\Api\Data\PolicyInterface;
use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Controller\Adminhtml\AbstractPolicy;
use Amasty\Gdpr\Model\Config\Source\Status;
use Amasty\Gdpr\Model\Policy;
use Amasty\Gdpr\Model\PolicyFactory;
use Amasty\Gdpr\Model\ResourceModel\Policy as PolicyResource;
use Amasty\Gdpr\Model\ResourceModel\PolicyContent as PolicyContentResource;
use Amasty\Gdpr\Model\ResourceModel\PolicyContent\Collection as ContentCollection;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Save extends AbstractPolicy
{
    /**
     * @var PolicyFactory
     */
    private $policyFactory;

    /**
     * @var PolicyRepositoryInterface
     */
    private $repository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var PolicyResource
     */
    private $policyResource;

    /**
     * @var PolicyContentResource
     */
    private $policyContentResource;

    /**
     * @var ContentCollection
     */
    private $contentCollection;

    public function __construct(
        Action\Context $context,
        PolicyFactory $policyFactory,
        PolicyRepositoryInterface $repository,
        DataPersistorInterface $dataPersistor,
        PolicyResource $policyResource,
        UserContextInterface $userContext,
        LoggerInterface $logger,
        ContentCollection $contentCollection,
        PolicyContentResource $policyContentResource
    ) {
        parent::__construct($context);
        $this->policyFactory = $policyFactory;
        $this->repository = $repository;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
        $this->userContext = $userContext;
        $this->policyResource = $policyResource;
        $this->policyContentResource = $policyContentResource;
        $this->contentCollection = $contentCollection;
    }

    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue('policy')) {
            $storeId = $this->getRequest()->getParam('store');
            try {
                if (isset($data['id'])) {
                    $model = $this->repository->getById($data['id']);
                } else {
                    /** @var Policy $model */
                    $model = $this->policyFactory->create();
                }

                if (!$this->validateModel($data, $storeId)) {
                    $this->messageManager->addErrorMessage(__('Privacy policy with the '
                        . 'same version already exists.'
                    ));
                    $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);

                    return;
                };

                if ($storeId) {
                    $policyContent = $this->contentCollection->findByStoreAndPolicy($data['id'], $storeId);

                    $useDefault = $this->getRequest()->getPostValue('use_default');
                    $useDefaultContent = isset($useDefault['content']) ? (bool)$useDefault['content'] : false;

                    if (!$useDefaultContent) {
                        $policyContent->addData([
                            'policy_id' => $data['id'],
                            'store_id' => $storeId,
                            'content' => $data['content']
                        ]);

                        $this->policyContentResource->save($policyContent);
                    } else {
                        if ($policyContent->getId()) {
                            $this->policyContentResource->delete($policyContent);
                        }
                    }

                    unset($data['content']);
                }

                $model->addData($data);
                $model->setLastEditedBy($this->userContext->getUserId());
                $this->repository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the item.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);

                    return;
                }
            } catch (\Exception $e) {
                if ($e instanceof LocalizedException) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } else {
                    $this->messageManager->addErrorMessage(__('An error has occurred'));
                    $this->logger->critical($e);
                }

                $this->dataPersistor->set('formData', $data);

                if ($id = (int)$this->getRequest()->getParam('id')) {
                    $this->_redirect('*/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('*/*/new');
                }

                return;
            }
        }

        $this->_redirect('*/*');
    }

    private function validateModel($policyData, $storeId) {
        if ($storeId !== null) {
            return true;
        }
        $versions = $this->policyResource->getAllValueFromColumnPolicy(PolicyInterface::POLICY_VERSION);
        foreach ($versions as $version) {
            if ($version['policy_version'] === $policyData['policy_version']
                && (!isset($policyData['id'])
                    || $version['id'] !== $policyData['id']
                )
            ) {
                return false;
            }
        }

        return true;
    }
}
