<?php
namespace Omnyfy\VendorSignUp\Block\Adminhtml\Vendor\Edit\Tab;

use \Magento\Backend\Block\Widget\Tab\TabInterface;
use Omnyfy\VendorSearch\Helper\MapSearchData;
use Omnyfy\VendorSignUp\Model\VendorKyc;
use Omnyfy\VendorSignUp\Model\SignUp;
use Magento\Framework\App\Request\DataPersistorInterface;

class KycInfo extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface {
	
	//protected $_template = 'signup/scripts.phtml';
	protected $kycFactory;

	protected $signUpFactory;

	protected $countryCode;

	protected $countrySource;

	protected $dataPersistor;
    protected $_mapSearchData;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Omnyfy\VendorSignUp\Model\VendorKycFactory $vendorKycFactory,
        \Omnyfy\VendorSignUp\Model\SignUpFactory $signUpFactory,
        \Omnyfy\VendorSignUp\Model\Source\CountryCode $countryCode,
        \Magento\Directory\Model\Config\Source\Country $countrySource,
        MapSearchData $mapSearchData,
        DataPersistorInterface $dataPersistor,
        array $data = []
    ) {
        $this->kycFactory = $vendorKycFactory;
        $this->signUpFactory = $signUpFactory;
        $this->countryCode = $countryCode;
		$this->countrySource = $countrySource;
		$this->dataPersistor = $dataPersistor;
        $this->_mapSearchData = $mapSearchData;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel() {
       return 'Registration Information';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle() {
        return 'Registration Information';
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden() {
        return false;
    }

    protected function _prepareForm() {
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
            // $model->setData('dob', $signUp->getDob());
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
        $registrationFieldset = $form->addFieldset('vendorsignup_regis_info', ['legend' => __('Registration Information')]);
        $taxInfoFieldSet = $form->addFieldset('vendorsignup_tax_info', ['legend' => __('Tax Information')]);
        $geoLocationFieldset = $form->addFieldset('vendorsignup_address_info', ['legend' => __('Address and Geolocation')]);

        $registrationFieldset->addField(
                'first_name', 'text', [
				'name' => 'first_name',
				'label' => __('First Name'),
				'title' => __('First Name'),
				'required' => true,
				'maxlength' => 100,
				'sortOrder' => 20,
                ]
        );

        $registrationFieldset->addField(
                'last_name', 'text', [
				'name' => 'last_name',
				'label' => __('Last Name'),
				'title' => __('Last Name'),
				'required' => true,
				'maxlength' => 100,
				'sortOrder' => 30,
                ]
        );

        $registrationFieldset->addField(
            'country_code', 'select', [
                'name' => 'country_code',
                'label' => __('Country Code'),
                'title' => __('Country Code'),
                'required' => true,
                'options' => $this->countryCode->_toArray(),
                'sortOrder' => 90,
            ]
        );

        $registrationFieldset->addField(
            'telephone', 'text', [
                'name' => 'telephone',
                'label' => __('Telephone'),
                'title' => __('Telephone'),
                'required' => true,
                'maxlength' => 100,
                'sortOrder' => 100,
            ]
        );

        $taxInfoFieldSet->addField(
            'legal_entity', 'text', [
                'name' => 'legal_entity',
                'label' => __('Legal Entity Name'),
                'title' => __('Legal Entity Name'),
                'required' => true,
                'sortOrder' => 120,
            ]
        );

        $taxInfoFieldSet->addField(
            'tax_number', 'select', [
                'name' => 'tax_number',
                'class' => 'country-check-tax',
                'label' => __('Tax Name'),
                'title' => __('Tax Name'),
                'required' => true,
                'sortOrder' => 130,
            ]
        );

        $taxInfoFieldSet->addField(
            'abn', 'text', [
                'name' => 'abn',
                'label' => __('Tax Number'),
                'title' => __('Tax Number'),
                'required' => true,
                'maxlength' => 100,
                'sortOrder' => 140,
            ]
        );

        $addressFieldType = 'text';
        if($this->_mapSearchData->isEnabled()){
            $geoLocationFieldset->addType(
                'textFieldWithBtn',
                \Omnyfy\VendorSignUp\Block\Adminhtml\TextFieldWithBtn\Renderer::class
            );
            $addressFieldType = 'textFieldWithBtn';
        }


        $geoLocationFieldset->addField(
                'business_address', $addressFieldType, [
				'name' => 'business_address',
				'label' => __('Business Address'),
				'title' => __('Business Address'),
				'required' => true,
				'sortOrder' => 40,
				'maxlength' => 200,
			]
        );

        $geoLocationFieldset->addField(
                'city', 'text', [
				'name' => 'city',
				'label' => __('City'),
				'title' => __('City'),
				'required' => true,
				'maxlength' => 100,
				'sortOrder' => 50,
                ]
        );

        $geoLocationFieldset->addField(
                'state', 'text', [
				'name' => 'state',
				'label' => __('State'),
				'title' => __('State'),
				'required' => true,
				'maxlength' => 100,
				'sortOrder' => 60,
                ]
        );
		
		$optionsc = $this->countrySource->toOptionArray();

        $geoLocationFieldset->addField(
                'country', 'select', [
				'name' => 'country',
				'label' => __('Country'),
				'title' => __('Country'),
				'required' => true,
				'values' => $optionsc,
				'sortOrder' => 70,
                ]
        );

        $geoLocationFieldset->addField(
                'postcode', 'text', [
				'name' => 'postcode',
				'label' => __('Postcode'),
				'title' => __('Postcode'),
				'required' => true,
				'maxlength' => 100,
				'sortOrder' => 80,
                ]
        );


        $geoLocationFieldset->addField(
            'latitude',
            'text',
            [
                'name' => 'latitude',
                'label' => __('Latitude'),
                'title' => __('Latitude'),
                'required' => false,
                'sortOrder' => 150,
            ]
        );

        $geoLocationFieldset->addField(
            'longitude',
            'text',
            [
                'name' => 'longitude',
                'label' => __('Longitude'),
                'title' => __('Longitude'),
                'required' => false,
                'sortOrder' => 160,
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
