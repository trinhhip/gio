<?php
namespace Omnyfy\VendorSignUp\Controller\Adminhtml\SignUp;

use Magento\Ui\Component\MassAction\Filter;

class MassReject extends \Omnyfy\VendorSignUp\Controller\Adminhtml\SignUp
{
    protected $status = 2;

    protected $_dataHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Omnyfy\VendorSignUp\Model\SignUpFactory $signUpFactory,
        \Omnyfy\VendorSignUp\Helper\Data $_dataHelper
    ) {
        $this->_dataHelper = $_dataHelper;
        parent::__construct($context, $logger, $coreRegistry, $resultPageFactory, $signUpFactory);
    }

    public function execute()
    {
        $ids = $this->getRequest()->getParam(Filter::SELECTED_PARAM);
        if ($ids) {
            $err = 0;
            $success = 0;
            foreach ($ids as $id) {
                try {
                    $model = $this->signUpFactory->create();
                    $model->load($id);
                    $model->setData('status', $this->status);
                    $model->save();				
    
                    //Send rejection email to vendor with credentials
                    $customerEmail = array(
                        "email" => trim($model->getEmail()),
                        "name" => $model->getBusinessName()
                    );
                    
                    $vars = [
                        'businessname' => $model->getBusinessName()
                    ];
                    $success++;
                    $this->_dataHelper->sendSignUpRejectToCustomer($vars, $customerEmail);

                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $err++;
                    $this->_logger->critical($e);
                } catch (\Exception $e) {
                    $err++;
                    $this->_logger->critical($e);
                }
            }
            if ($err) {
                $this->messageManager->addErrorMessage($err . ' records reject false.');
            }
            if ($success) {
                $this->messageManager->addSuccessMessage($success . ' records reject successfully.');
            }
        } else {
            $this->messageManager->addErrorMessage('We can\'t find the sign up request.');
        }
        $this->_redirect('omnyfy_vendorsignup/signup/listing');
    }
}