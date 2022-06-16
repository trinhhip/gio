<?php


namespace OmnyfyCustomzation\Vendor\Block\Adminhtml\Vendor\Edit\Tab;


use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Omnyfy\VendorSignUp\Model\SignUpFactory;
use Omnyfy\VendorSignUp\Model\Source\CountryCode;
use Omnyfy\VendorSignUp\Model\VendorKycFactory;

class Banking extends Generic implements TabInterface
{
    protected $kycFactory;

    protected $signUpFactory;

    protected $countryCode;

    protected $countrySource;

    protected $dataPersistor;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        VendorKycFactory $vendorKycFactory,
        SignUpFactory $signUpFactory,
        CountryCode $countryCode,
        Country $countrySource,
        DataPersistorInterface $dataPersistor,
        array $data = []
    )
    {
        $this->kycFactory = $vendorKycFactory;
        $this->signUpFactory = $signUpFactory;
        $this->countryCode = $countryCode;
        $this->countrySource = $countrySource;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return 'Banking Information';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return 'Banking Information';
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

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_omnyfy_vendor_vendor');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('vendor_');

        $kyc = $this->kycFactory->create();
        $kyc->load($model->getId(), 'vendor_id');

        $signUp = $this->signUpFactory->create();
        $signUp->load($kyc->getSignupId());
        if (!empty($signUp->getId())) {
            $model->setData('first_name', $signUp->getFirstName());
            $model->setData('last_name', $signUp->getLastName());
            $model->setData('dob', $signUp->getDob());
            $model->setData('business_address', $signUp->getBusinessAddress());
            $model->setData('city', $signUp->getCity());
            $model->setData('state', $signUp->getState());
            $model->setData('country', $signUp->getCountry());
            $model->setData('postcode', $signUp->getPostcode());
            $model->setData('country_code', $signUp->getCountryCode());
            $model->setData('telephone', $signUp->getTelephone());
            $model->setData('kyc_email', $signUp->getEmail());
            $model->setData('legal_entity', $signUp->getLegalEntity());
            $model->setData('tax_number', $signUp->getTaxNumber());
            $model->setData('abn', $signUp->getAbn());
        }
        $fieldset = $form->addFieldset('Vendor_banking_info', ['legend' => __('Banking Information')]);

        $fieldset->addField(
            'bank_name', 'text', [
                'name' => 'bank_name',
                'label' => __('Bank Name'),
                'title' => __('Bank Name'),
                'required' => false,
                'maxlength' => 255,
                'sortOrder' => 10,
            ]
        );

        $fieldset->addField(
            'bank_address', 'text', [
                'name' => 'bank_address',
                'label' => __('Bank Address'),
                'title' => __('Bank Address'),
                'required' => false,
                'maxlength' => 255,
                'sortOrder' => 20,
            ]
        );

        $fieldset->addField(
            'bank_swift', 'text', [
                'name' => 'bank_swift',
                'label' => __('SWIFT'),
                'title' => __('SWIFT'),
                'required' => false,
                'sortOrder' => 30,
                'maxlength' => 200,
            ]
        );

        $fieldset->addField(
            'bank_account_name', 'text', [
                'name' => 'bank_account_name',
                'label' => __('Account Name'),
                'title' => __('Account Name'),
                'required' => false,
                'maxlength' => 255,
                'sortOrder' => 40,
            ]
        );

        $fieldset->addField(
            'bank_account_number', 'text', [
                'name' => 'bank_account_number',
                'label' => __('Account Number'),
                'title' => __('Account Number'),
                'required' => false,
                'maxlength' => 255,
                'sortOrder' => 50,
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
