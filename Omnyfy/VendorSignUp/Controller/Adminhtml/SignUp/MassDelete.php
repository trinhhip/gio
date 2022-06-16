<?php
namespace Omnyfy\VendorSignUp\Controller\Adminhtml\SignUp;

use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends \Omnyfy\VendorSignUp\Controller\Adminhtml\SignUp
{
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
                    $model->delete();				
                    $success++;
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_logger->critical($e);
                    $err++;
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    $err++;
                }
            }
            if ($err) {
                $this->messageManager->addErrorMessage(
                    $err . ' records deleted false'
                );
            }
            if ($success) {
                $this->messageManager->addSuccessMessage( $success .' records deleted successfully!');
            }
        } else {
            $this->messageManager->addErrorMessage('We can\'t find the delete request.');
        }

        $this->_redirect('omnyfy_vendorsignup/signup/listing');
    }
}