<?php

namespace OmnyfyCustomzation\BuyerApproval\Block\Adminhtml\Edit;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;

/**
 * Class Approve
 *
 * @package OmnyfyCustomzation\BuyerApproval\Block\Adminhtml\Edit
 */
class Approve extends GenericButton implements ButtonProviderInterface
{


    /**
     * @return array|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getButtonData()
    {
        if (!$this->helperData->shouldEnableButton(AttributeOptions::APPROVED)) {
            return [];
        }

        $customerId = $this->helperData->getRequestParam('id');
        $this->helperData->setPendingCustomer($customerId);

        $data = [];

        if (!$this->authorization->isAllowed('OmnyfyCustomzation_BuyerApproval::approval')) {
            return $data;
        }

        if ($customerId) {
            $data = [
                'label' => __('Approve Buyer'),
                'class' => 'reset reset-password',
                'on_click' => sprintf("location.href = '%s';", $this->getApproveUrl()),
                'sort_order' => 65,
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getApproveUrl()
    {
        return $this->getUrl(
            'buyerapproval/index/approve',
            ['id' => $this->getCustomerId(), 'status' => AttributeOptions::APPROVED]
        );
    }
}
