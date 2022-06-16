<?php
namespace Omnyfy\VendorSignUp\Controller\Adminhtml\SignUp;

/**
 * Sfmc template controller
 */
class Save extends \Omnyfy\VendorSignUp\Controller\Adminhtml\SignUp
{
    protected $_dataHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    protected $dataMap = [
        'business_name' => 'business_name',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'business_address' => 'business_address',
        'city' => 'city',
        'state' => 'state',
        'country' => 'country',
        'postcode' => 'postcode',
        'country_code' => 'country_code',
        'telephone' => 'telephone',
        'email' => 'email',
        'legal_entity' => 'legal_entity',
        'tax_number' => 'tax_number',
        'abn' => 'abn',
        'description' => 'description',
        'payout_basis_type' => 'payout_basis_type'
    ];

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Omnyfy\VendorSignUp\Model\SignUpFactory $signUpFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Omnyfy\VendorSignUp\Helper\Data $_dataHelper
    ) {
        $this->_dataHelper = $_dataHelper;
        parent::__construct($context, $logger, $coreRegistry, $resultPageFactory, $signUpFactory);
        $this->storeManager = $storeManager;
    }



    public function execute() {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/*'));
        }

        foreach ($request->getPostValue() as $key => $value) {
            if (strpos($key,'extend_attribute_') !== false) {
                $data['extend_attribute'][str_replace('extend_attribute_','',$key)] = $value;
            } else {
                $data[$key] = $value;
            }
        }
        $model = $this->signUpFactory->create();

        try {
			if($data['tax_number']=='ABN'){
				if (!$this->_dataHelper->isValidAbn($data['abn'])) {
					throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a valid ABN number.'));
				}
			}

            $inputFilter = new \Zend_Filter_Input(
                    [], [], $data
            );
            $data = $inputFilter->getUnescaped();

            if (isset($data['id']) && !empty($data['id'])) {
                $id = $data['id'];
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong signup data is specified.'));
                    }
                }
				if ($this->_dataHelper->isAdminAccountExist($data['email']) && $data['email']!=$model->getEmail()) {
					throw new \Magento\Framework\Exception\LocalizedException(
					    __('This email address is already been used. Please use another email or login with your existing vendor account.')
                    );
				}
				$successMessage = 'You have successfully updated "'.$model->getBusinessName().'" signup request.';
            } else {
				$successMessage = 'You saved the signup data.';
                unset($data['id']);
            }

            $this->_eventManager->dispatch('omnyfy_vendor_signup_backend_form_save_before', ['data' => $data, 'sign_up' => $model]);

            unset($data['form_key']);
            $this->saveSignup($data);
            $this->_session->setPageData($data);

            $this->_eventManager->dispatch('omnyfy_vendor_signup_backend_form_save_after', ['data' => $data, 'sign_up' => $model]);

            $this->messageManager->addSuccessMessage($successMessage);
            $this->_session->setPageData(false);
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                return;
            }
            $this->_redirect('*/*/view', ['id' => $model->getId()]);
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = (int) $this->getRequest()->getParam('id');
            if (!empty($id)) {
                $this->_redirect('*/*/edit', ['id' => $id]);
            }
            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                'Something went wrong while saving the template data. Please review the error log.'
            );
            $this->_logger->critical($e);
            $this->_session->setPageData($data);
            if (isset($data['id'])) {
                $this->_redirect('*/*/edit', ['id' => $data['id']]);
            } else if ($model->getId()) {
                $this->_redirect('*/*/edit', ['id' => $model->getId()]);
            } else {
                $this->_redirect('*/*/edit', ['id' => '']);
            }

            return;
        }
        $this->_redirect('*/*/');
    }

    public function saveSignup($data)
    {
        $dataObj = [
            'status' => '0',
            'created_by' => 'Customer'
        ];
        $extendAttr = isset($data['extend_attribute']) ? $data['extend_attribute'] : [];
        if(!empty($extendAttr)){
            $dataObj['extend_attribute'] = json_encode($extendAttr);
        }
        foreach($this->dataMap as $from => $to) {
            $dataObj[$to] = array_key_exists($from, $data) ? $data[$from] : null;
        }

        //attributes in form to save as extended information
        $extra = [];
        foreach($data as $key => $value) {
            if (array_key_exists($key, $this->dataMap) || $key == 'id') {
                continue;
            }
            $extra[$key] = $value;
        }

        $signUp = $this->signUpFactory->create();
        $signUp->load($data['id']);
        $extraInfo = $signUp->getExtraInfoAsArray();
        $extraInfo['extend_attribute'] = !empty($extra['extend_attribute']) ? $extra['extend_attribute'] : [];
        $dataObj['extra_info'] = json_encode($extraInfo);
        foreach ($dataObj as $field => $value) {
            $signUp->setData($field, $value);
        }
        $signUp->save();

        return $signUp;
    }
}
