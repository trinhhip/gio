<?php

namespace Omnyfy\VendorSignUp\Block\Adminhtml\SignUp\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;


class Main extends Generic implements TabInterface
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;
    /**
     * @var \Omnyfy\VendorSignUp\Model\SignUpFactory
     */
    private $signUpFactory;
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    private $country;
    /**
     * @var \Omnyfy\VendorSignUp\Model\Source\CountryCodeList
     */
    private $countryCodeList;
    /**
     * @var \Omnyfy\VendorSignUp\Model\Source\TaxName
     */
    private $taxName;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;
    /**
     * @var \Omnyfy\Mcm\Model\Config\Source\PayoutBasisTyp
     */
    protected $payoutBasisType;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Omnyfy\VendorSignUp\Model\SignUpFactory $signUpFactory,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Omnyfy\VendorSignUp\Model\Source\CountryCodeList $countryCodeList,
        \Omnyfy\VendorSignUp\Model\Source\TaxName $taxName,
        \Magento\Eav\Model\Config $eavConfig,
        \Omnyfy\Mcm\Model\Config\Source\PayoutBasisType $payoutBasisType,
        \Psr\Log\LoggerInterface $logger,
        array $data = [])
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->formFactory = $formFactory;
        $this->signUpFactory = $signUpFactory;
        $this->country = $country;
        $this->countryCodeList = $countryCodeList;
        $this->taxName = $taxName;
        $this->eavConfig = $eavConfig;
        $this->payoutBasisType = $payoutBasisType;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Profile');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Profile');
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

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $extendAttributes = [];
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $model = $this->signUpFactory->create();
            $model->load($id);
            $extraInfo = $model->getExtraInfoAsArray();
            if (isset($extraInfo['extend_attribute'])) {
                $extendAttributes = array_keys($extraInfo['extend_attribute']);
            }
            /** @var \Magento\Framework\Data\Form $form */
            $form = $this->_formFactory->create();
            $fieldset = $form->addFieldset(
                'general',
                []
            );
            $fieldset->addField(
                'id',
                'hidden',
                [
                    'name' => 'id',
                    'required' => true,
                    'value' => $model->getId()
                ]
            );
            $fieldset->addField(
                'first_name',
                'text',
                [
                    'name' => 'first_name',
                    'label' => 'First Name',
                    'title' => 'First Name',
                    'required' => true,
                    'value' => $model->getFirstName()
                ]
            );
            $fieldset->addField(
                'last_name',
                'text',
                [
                    'name' => 'last_name',
                    'label' => 'Last Name',
                    'title' => 'Last Name',
                    'required' => true,
                    'value' => $model->getLastName()
                ]
            );

            $fieldset->addField(
                'payout_basis_type',
                'select',
                [
                    'name' => 'payout_basis_type',
                    'label' => 'Payout Basis Type',
                    'title' => 'Payout Basis Type',
                    'required' => true,
                    'value' => $model->getPayoutBasisType(),
                    'values' => $this->payoutBasisType->toOptionArray()
                ]
            );
//            $fieldset->addField(
//                'dob',
//                'date',
//                [
//                    'name' => 'dob',
//                    'label' => 'Date of Birth',
//                    'title' => 'Date of Birth',
//                    'required' => false,
//                    'value' => $model->getDob(),
//                    'date_format' => 'yyyy-MM-dd'
//                ]
//            );
            $fieldset->addField(
                'business_name',
                'text',
                [
                    'name' => 'business_name',
                    'label' => 'Business Name',
                    'title' => 'Business Name',
                    'required' => true,
                    'value' => $model->getBusinessName()
                ]
            );
            $fieldset->addField(
                'business_address',
                'text',
                [
                    'name' => 'business_address',
                    'label' => 'Business Address',
                    'title' => 'Business Address',
                    'required' => true,
                    'value' => $model->getBusinessAddress()
                ]
            );
            $fieldset->addField(
                'city',
                'text',
                [
                    'name' => 'city',
                    'label' => 'City',
                    'title' => 'City',
                    'required' => true,
                    'value' => $model->getCity()
                ]
            );
            $fieldset->addField(
                'state',
                'text',
                [
                    'name' => 'state',
                    'label' => 'State',
                    'title' => 'State',
                    'required' => true,
                    'value' => $model->getState()
                ]
            );
            $fieldset->addField(
                'country',
                'select',
                [
                    'name' => 'country',
                    'label' => 'Country',
                    'title' => 'Country',
                    'required' => true,
                    'value' => $model->getCountry(),
                    'values' => $this->country->toOptionArray()
                ]
            );
            $fieldset->addField(
                'postcode',
                'text',
                [
                    'name' => 'postcode',
                    'label' => 'Postcode',
                    'title' => 'Postcode',
                    'required' => true,
                    'value' => $model->getPostcode()
                ]
            );
            $fieldset->addField(
                'country_code',
                'text',
                [
                    'name' => 'country_code',
                    'label' => 'Country Code',
                    'title' => 'Country Code',
                    'required' => true,
                    'value' => $model->getCountryCode(),
                    'values' => $this->countryCodeList->toOptionArray()
                ]
            );
            $fieldset->addField(
                'telephone',
                'text',
                [
                    'name' => 'telephone',
                    'label' => 'Telephone',
                    'title' => 'Telephone',
                    'required' => true,
                    'value' => $model->getTelephone()
                ]
            );
            $fieldset->addField(
                'email',
                'text',
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'title' => 'Email',
                    'required' => true,
                    'value' => $model->getEmail()
                ]
            );
            $fieldset->addField(
                'legal_entity',
                'text',
                [
                    'name' => 'legal_entity',
                    'label' => 'Legal Entity',
                    'title' => 'Legal Entity',
                    'required' => true,
                    'value' => $model->getLegalEntity()
                ]
            );
            $fieldset->addField(
                'tax_number',
                'select',
                [
                    'name' => 'tax_number',
                    'label' => 'Tax Name',
                    'title' => 'Tax Name',
                    'required' => true,
                    'value' => $model->getTaxNumber(),
                    'values' => $this->taxName->toOptionArray()
                ]
            );
            $fieldset->addField(
                'abn',
                'text',
                [
                    'name' => 'abn',
                    'label' => 'Tax Number',
                    'title' => 'Tax Number',
                    'required' => true,
                    'value' => $model->getAbn()
                ]
            );
            $fieldset->addField(
                'description',
                'textarea',
                [
                    'name' => 'description',
                    'label' => 'Business Description',
                    'title' => 'Business Description',
                    'required' => true,
                    'value' => $model->getDescription()
                ]
            );


            foreach ($extendAttributes as $extendAttribute) {
                try {
                    $isSkip = false;
                    $frontendInput = '';
                    $vendorAttributes = $this->eavConfig->getAttribute('omnyfy_vendor_vendor', $extendAttribute);
                    $data = [
                        'name' => 'extend_attribute_'.$extendAttribute,
                        'label' => __($vendorAttributes->getFrontend()->getLabel()),
                        'title' => __($vendorAttributes->getFrontend()->getLabel())
                    ];
                    $vendorAttributes = $this->eavConfig->getAttribute('omnyfy_vendor_vendor', $extendAttribute);
                    $data['required'] = $vendorAttributes->getIsRequired() ? true : false;
                    if ($vendorAttributes->getFrontendInput() == 'textarea'
                        || $vendorAttributes->getFrontendInput() == 'text') {
                        $data['value'] = $extraInfo['extend_attribute'][$extendAttribute];

                    } elseif ($vendorAttributes->getFrontendInput() == 'multiselect'
                        || $vendorAttributes->getFrontendInput() == 'select') {
                        $data['value'] = $extraInfo['extend_attribute'][$extendAttribute];
                        $data['values'] = $vendorAttributes->getSource()->getAllOptions();
                    } elseif ($vendorAttributes->getFrontendInput() == 'date') {
                        $data['value'] = $extraInfo['extend_attribute'][$extendAttribute];
                        $data['date_format'] = 'yyyy-MM-dd';
                    } elseif ($vendorAttributes->getFrontendInput() == 'datetime') {
                        $isSkip = true;
                        $data['value'] = $extraInfo['extend_attribute'][$extendAttribute];
                        $data['date_format'] = 'yyyy-MM-dd';
                        $data['time_format'] = 'HH:mm';
                        $frontendInput = 'date';
                    } elseif ($vendorAttributes->getFrontendInput() == 'boolean') {
                        $isSkip = true;
                        $data['value'] = $extraInfo['extend_attribute'][$extendAttribute];
                        $data['values'] = $vendorAttributes->getSource()->getAllOptions();
                        $frontendInput = 'select';
                    }
                    if ($isSkip) {
                        $fieldset->addField(
                            $extendAttribute,
                            $frontendInput,
                            $data
                        );
                    } else {
                        $fieldset->addField(
                            $extendAttribute,
                            $vendorAttributes->getFrontendInput(),
                            $data
                        );
                    }
                } catch (\Exception $e) {
                    $this->logger->critical('Cannot add field "'.$extendAttribute.'"into fieldset');
                    continue;
                }
            }
            $this->setForm($form);
        }
        return parent::_prepareForm();
    }

}
