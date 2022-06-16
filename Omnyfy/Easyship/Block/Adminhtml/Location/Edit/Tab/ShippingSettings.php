<?php
namespace Omnyfy\Easyship\Block\Adminhtml\Location\Edit\Tab;

class ShippingSettings extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $accountListFactory;
    protected $easyVendorLocFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Omnyfy\Easyship\Model\EasyshipAccountFactory $accountListFactory,
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyVendorLocFactory,
        array $data = []
    ){
        parent::__construct($context, $registry, $formFactory, $data);
        $this->accountListFactory = $accountListFactory;
        $this->easyVendorLocFactory = $easyVendorLocFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Shipping Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Shipping Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareLayout()
    {
        $model = $this->_coreRegistry->registry('current_omnyfy_vendor_location');
        $this->pageConfig->getTitle()->set(__('Add Location'));
        if ($model->getId()) {
            $this->pageConfig->getTitle()->set(__('Edit Location'));
        }
        return parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_omnyfy_vendor_location');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('location_');

        $vendorInfo = $this->_backendSession->getVendorInfo();
        $vendorId = null;
        if (empty($vendorInfo)) {
            $vendorId = $model->getVendorId();
        }else{
            $vendorId = $vendorInfo['vendor_id'];
        }

        if ($model->getId()) {
            $easyAccount = $this->easyVendorLocFactory->create()->getLocationAccount($model->getId());
            if ($easyAccount != null) {
                $model->setData('easyship_account_id', $easyAccount->getEasyshipAccountId());
                $model->setData('easyship_address_id', $easyAccount->getEasyshipAddressId());
            }
        }
        
        $list = $this->accountListFactory->create()->getAccountListByVendorAndCountry($vendorId, $model->getCountry());
        $arrAccount = [];
        $arrAccount[null] = '-- Select Easyship Account --';
        if (count($list) > 0) {
            foreach ($list as $value) {
                $arrAccount[$value['entity_id']] = $value['name'];
            }
        }

        $fieldset = $form->addFieldset('location_access_token', ['legend' => __('Easyship Account')]);

        $accountName = $fieldset->addField(
            'easyship_account_id',
            'select',
            [
                'name' => 'easyship_account_id',
                'label' => __('Select Easyship Account'),
                'title' => __('Select Easyship Account'),
                'values' => $arrAccount,
                'data-form-part' => $this->getData('target_form')
            ]
        );

        $easyshipAddressId = $fieldset->addField(
            'easyship_address_id',
            'text',
            [
                'name' => 'easyship_address_id',
                'label' => __('Easyship Address Id'),
                'title' => __('Easyship Address Id'),
                'readonly' => true,
                'data-form-part' => $this->getData('target_form')
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function isAjaxLoaded()
    {
        return false;
    }
}