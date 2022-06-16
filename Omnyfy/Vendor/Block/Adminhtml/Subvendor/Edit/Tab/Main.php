<?php

namespace Omnyfy\Vendor\Block\Adminhtml\Subvendor\Edit\Tab;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\OptionInterface;

/**
 * Cms page edit form main tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    const CURRENT_USER_PASSWORD_FIELD = 'current_password';

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_LocaleLists;

    /**
     * Operates with deployed locales.
     *
     * @var OptionInterface
     */
    private $deployedLocales;

    protected $_omnyfyVendors;

    protected $session;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     * @param OptionInterface $deployedLocales Operates with deployed locales.
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory$omnyfyVendors,
        \Magento\Backend\Model\Session $session,
        array $data = [],
        OptionInterface $deployedLocales = null
    ) {
        $this->_authSession = $authSession;
        $this->_LocaleLists = $localeLists;
        $this->_omnyfyVendors = $omnyfyVendors;
        $this->session = $session;
        $this->deployedLocales = $deployedLocales
            ?: ObjectManager::getInstance()->get(OptionInterface::class);
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $model = $this->_coreRegistry->registry('permissions_user');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Account Information')]);

        if ($model->getUserId()) {
            $baseFieldset->addField('user_id', 'hidden', ['name' => 'user_id']);
        } else {
            if (!$model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }

        $baseFieldset->addField(
            'username',
            'text',
            [
                'name' => 'username',
                'label' => __('User Name'),
                'id' => 'username',
                'title' => __('User Name'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'firstname',
            'text',
            [
                'name' => 'firstname',
                'label' => __('First Name'),
                'id' => 'firstname',
                'title' => __('First Name'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'lastname',
            'text',
            [
                'name' => 'lastname',
                'label' => __('Last Name'),
                'id' => 'lastname',
                'title' => __('Last Name'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'id' => 'customer_email',
                'title' => __('User Email'),
                'class' => 'required-entry validate-email',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'interface_locale',
            'select',
            [
                'name' => 'interface_locale',
                'label' => __('Interface Locale'),
                'title' => __('Interface Locale'),
                'values' => $this->deployedLocales->getOptionLocales(),
                'class' => 'select'
            ]
        );

        if ($this->_authSession->getUser()->getId() != $model->getUserId()) {
            $baseFieldset->addField(
                'is_active',
                'select',
                [
                    'name' => 'is_active',
                    'label' => __('This account is'),
                    'id' => 'is_active',
                    'title' => __('Account Status'),
                    'class' => 'input-select',
                    'options' => ['1' => __('Active'), '0' => __('Inactive')]
                ]
            );
        }


        $vendorInfo = $this->session->getVendorInfo();
        if (empty($vendorInfo) || !isset($vendorInfo['vendor_id']) || 0 == $vendorInfo['vendor_id']) {
            $vendorCollection = $this->_omnyfyVendors->create();

            foreach($vendorCollection as $vendor) {
                $vendorArray[$vendor->getId()] = __($vendor->getName());
            }

            // if admin need to add field with vendor dropdowns
            $baseFieldset->addField(
                'parent_vendor_id',
                'select',
                [
                    'name' => 'parent_vendor_id',
                    'label' => __('Parent Vendor'),
                    'id' => 'parent_vendor_id',
                    'options' => $vendorArray
                ]
            );

        } else {
            $baseFieldset->addField(
                'parent_vendor_id',
                'hidden',
                [
                    'name' => 'parent_vendor_id',
                    'value' => $vendorInfo['vendor_id']
                ]
            );
        }

        $baseFieldset->addField('user_roles', 'hidden', ['name' => 'user_roles', 'id' => '_user_roles']);

        $currentUserVerificationFieldset = $form->addFieldset(
            'current_user_verification_fieldset',
            ['legend' => __('Current User Identity Verification')]
        );
        $currentUserVerificationFieldset->addField(
            self::CURRENT_USER_PASSWORD_FIELD,
            'password',
            [
                'name' => self::CURRENT_USER_PASSWORD_FIELD,
                'label' => __('Your Password'),
                'id' => self::CURRENT_USER_PASSWORD_FIELD,
                'title' => __('Your Password'),
                'class' => 'input-text validate-current-password required-entry',
                'required' => true
            ]
        );

        $data = $model->getData();

        if (!empty($vendorInfo) || isset($vendorInfo['vendor_id'])) {
            $data['parent_vendor_id'] =  ['value' => $vendorInfo['vendor_id']];
        }

        unset($data['password']);
        unset($data[self::CURRENT_USER_PASSWORD_FIELD]);
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
