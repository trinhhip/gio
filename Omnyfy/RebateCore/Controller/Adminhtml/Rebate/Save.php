<?php

namespace Omnyfy\RebateCore\Controller\Adminhtml\Rebate;

use Exception;
use Omnyfy\RebateCore\Api\Data\IRebateRepository;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use RuntimeException;

/**
 * Class Save
 * @package Omnyfy\RebateCore\Controller\Adminhtml\Rebate
 */
class Save extends Action
{
    /**
     * @var FileUploader
     */
    protected $fileUploader;
    /**
     * @var RebateCoreRepository
     */
    protected $rebateRepository;
    /**
     * @var DateTime
     */
    protected $dateTime;


    /**
     * Save constructor.
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        DateTime $dateTime,
        IRebateRepository $rebateRepository
    )
    {

        parent::__construct($context);
        $this->rebateRepository = $rebateRepository;
        $this->dateTime = $dateTime;

    }

    /**
     * @return mixed
     */
    public function execute()
    {

        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $id = $this->getRequest()->getParam('entity_id') ?? null;
            $rebateRepository = $this->rebateRepository->getRebate($id);

            try {
                if (empty($data['entity_id'])) {
                    $data['entity_id'] = null;
                } else {
                    $data['updated_at'] = $this->dateTime->gmtDate();
                }
                if (!empty($data['end_date'])) {
                    $data['end_date'] = date('Y-m-d 23:59:59', strtotime($data['end_date']));
                    $data['start_date'] = date('Y-m-d 00:00:00', strtotime('+1 day', strtotime($data['end_date'])));
                }
                $rebateRepository->setData($data);
                $this->rebateRepository->saveRebate($rebateRepository);
                if ($this->rebateRepository->saveRebate($rebateRepository)) {
                    $rebateId = $rebateRepository->getEntityId();
                    usort($data['rebate_contribution_dynamic_rows'], $this->build_sorter('position'));
                    if (!empty($data['rebate_contribution_dynamic_rows'])) {
                        $this->rebateRepository->deleteContributionsData($rebateId);
                        $this->rebateRepository->insertValues($rebateId, $data['rebate_contribution_dynamic_rows']);
                    }
                }
                $this->messageManager->addSuccess(__('Product Rebate saved'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('rebate_ui/index/edit', ['entity_id' => $rebateRepository->getEntityId()]);
                }
                return $resultRedirect->setPath('rebate_ui/index/index');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the rebate'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('rebate_ui/index/edit', ['entity_id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('rebate_ui/index/edit');
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('omnyfy_rebatecore::rebate_save');
    }

    protected function build_sorter($key)
    {
        return function ($a, $b) use ($key) {
            if ($a[$key] == $b[$key]) return 0;
            return ($a[$key] < $b[$key]) ? -1 : 1;
        };
    }


}
