<?php

namespace Omnyfy\VendorSignUp\Block\Adminhtml;

class SignUp extends \Magento\Backend\Block\Widget
{

    private $_enquiryData;

    protected $pricing;

    protected $_signUpHelper;

    protected $_backendHelper;
    /**
     * @var \Omnyfy\Vendor\Helper\Attribute
     */
    private $attrHelper;
    /**
     * @var \Omnyfy\VendorSubscription\Model\PlanFactory
     */
    private $planFactory;

    protected $payoutBasisType;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricing,
        \Omnyfy\VendorSignUp\Helper\Data $signUpHelper,
        \Omnyfy\VendorSignUp\Helper\Backend $backendHelper,
        \Omnyfy\Vendor\Helper\Attribute $attrHelper,
        \Omnyfy\VendorSubscription\Model\PlanFactory $planFactory,
        \Omnyfy\Mcm\Model\Config\Source\PayoutBasisType $payoutBasisType,
        array $data = []
    )
    {
        $this->pricing = $pricing;
        $this->_signUpHelper = $signUpHelper;
        $this->_backendHelper = $backendHelper;
        parent::__construct($context, $data);

        $this->attrHelper = $attrHelper;
        $this->planFactory = $planFactory;
        $this->payoutBasisType = $payoutBasisType;
    }

    public function getDateWithFormat($date, $format = 'd/m/Y')
    {
        return $date ? $this->_localeDate->date(new \DateTime($date))->format($format) : '';
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/listing');
    }

    /**
     * Get URL for approve button
     *
     * @return string
     */
    public function getApproveUrl()
    {
        return $this->getUrl('*/*/approve', ['id' => $this->getSignUpId()]);
    }

    /**
     * Get URL for reject button
     *
     * @return string
     */
    public function getRejectUrl()
    {
        return $this->getUrl('*/*/reject', ['id' => $this->getSignUpId()]);
    }

    /**
     * Get URL for edit button
     *
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl('*/*/edit', ['id' => $this->getSignUpId()]);
    }

    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'back', 'Magento\Backend\Block\Widget\Button', [
                'label' => __('Back'),
                'data_attribute' => [
                    'role' => 'back',
                ],
                'class' => 'action-default scalable back',
                'onclick' => sprintf("location.href = '%s';", $this->getBackUrl()),
            ]
        );
        if ($this->checkStatus() != '1') {
            $this->getToolbar()->addChild(
                'approve', 'Magento\Backend\Block\Widget\Button', [
                    'label' => __('Approve'),
                    'data_attribute' => [
                        'role' => 'approve',
                    ],
                    'class' => 'action-default show-event-loader scalable save',
                    'onclick' => sprintf("location.href = '%s';", $this->getApproveUrl()),
                ]
            );
        }
        if ($this->checkStatus() == '0') {
            $this->getToolbar()->addChild(
                'reject', 'Magento\Backend\Block\Widget\Button', [
                    'label' => __('Reject'),
                    'data_attribute' => [
                        'role' => 'reject',
                    ],
                    'class' => 'action-default show-event-loader scalable save',
                    'onclick' => sprintf("location.href = '%s';", $this->getRejectUrl()),
                ]
            );
            $this->getToolbar()->addChild(
                'edit', 'Magento\Backend\Block\Widget\Button', [
                    'label' => __('Edit'),
                    'data_attribute' => [
                        'role' => 'edit',
                    ],
                    'class' => 'action-default show-event-loader scalable save',
                    'onclick' => sprintf("location.href = '%s';", $this->getEditUrl()),
                ]
            );
        }
        return parent::_prepareLayout();
    }

    public function getSignUpId()
    {
        return $this->getRequest()->getParam('id');
    }

    public function getVendorType()
    {
        $vendorTypeId = $this->getSignUp()->getData('vendor_type_id');
        if (isset($vendorTypeId)) {
            return $this->_backendHelper->getVendorType($vendorTypeId)->getTypeName();
        } else {
            return '';
        }
    }

    public function getSubscriptionPlan() {
        $extraInfo = $this->getSignUp()->getData('extra_info');
        if (empty($extraInfo)) {
            $extraInfo = [];
        }
        try {
            $extraInfo = json_decode($extraInfo, true);
        } catch (\Exception $e) {
            $extraInfo = [];
        }
        if (isset($extraInfo['plan_role_id'])) {
            list($planId) = explode('_', $extraInfo['plan_role_id']);
            return $this->planFactory->create()->load($planId)->getPlanName();
        } else {
            return '';
        }
    }

    public function getSignUp()
    {
        return $this->_backendHelper->getSignUpById($this->getSignUpId());
    }

    public function getVendorId()
    {
        $kyc = $this->_backendHelper->getKycBySignUpId($this->getSignUpId());
        return empty($kyc) ? false : $kyc->getVendorId();
    }

    public function checkStatus()
    {
        return $this->getSignUp()->getStatus();
    }

    public function getStatusLabel($status)
    {
        if ($status == '0') {
            return 'New';
        } else if ($status == '1') {
            return 'Approved';
        } else if ($status == '2') {
            return 'Rejected';
        }
    }

    public function getAttributeVendorHtml($vendorSignUp)
    {
        $vendorAttributes = $this->attrHelper->getVendorSignUpAttributes($vendorSignUp->getData('vendor_type_id'))->getItems();
        $extendAttribute = $vendorSignUp->getData('extend_attribute');
        $extendAttributeArray = json_decode($extendAttribute, true);
        $html = '';
        foreach ($vendorAttributes as $attribute) {
            $value = isset($extendAttributeArray[$attribute->getAttributeCode()]) ? $extendAttributeArray[$attribute->getAttributeCode()] : '';
            if ($attribute->getFrontendInput() == 'select') {
                $value = $attribute->getSource()->getOptionText($value);
            }

            /** Check if the extend_attribute is multiselect and display the options accordingly *
             * $value is an array in this case **/
           if ($attribute->getFrontendInput() == 'multiselect' && $value){
                $optionString = "";
                foreach ($value as $item) {
                    $optionString .= $attribute->getSource()->getOptionText($item) . ", ";
                }
                $value = rtrim($optionString, ", ");
            }

            $html .= "<tr><th>" . $attribute->getData('frontend_label') . "</th><td>" . $value . "</td>";
        }
        return $html;
    }

    public function getOptionPayoutBasisType() {
        return $this->payoutBasisType->toOptionArray();
    }

    public function getLabelPayoutBasisType($value){
        $options = $this->getOptionPayoutBasisType();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
    }
}
