<?php

namespace OmnyfyCustomzation\Newsletter\Plugin\Magento\Newsletter\Controller\Subscriber;


use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class NewAction
 *
 * @package NewBalance\Newsletter\Plugin\Controller\Subscriber
 */
class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param $subject
     * @param $proceed
     *
     * @return Json
     */
    public function aroundExecute($subject, $proceed)
    {
        $resultJsonFactory = ObjectManager::getInstance()->get(JsonFactory::class);

        if (!$this->getRequest()->isAjax()) {
            return $proceed();
        }

        $response = [];
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = trim($this->getRequest()->getPost('email'));
            $subscriber = $this->_subscriberFactory->create()
                ->loadByEmail($email);

            if ($subscriber->getId()) {
                return $resultJsonFactory->create()->setData([
                    'status' => 'ERROR',
                    'success' => false,
                    'msg' => __('This email address has already subscribed. Please try again'),
                ]);
            }
            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $this->_subscriberFactory->create()->subscribe($email);

                if (!empty($name)) {
                    $this->_subscriberFactory->create()
                        ->loadByEmail($email)
                        ->setSubscriberName($name)->save();
                }
                $response = [
                    'success' => true,
                    'msg' => __('Thank you for signing up! Stay tuned for our upcoming launch.'),
                ];
            } catch (LocalizedException $e) {
                $response = [
                    'success' => true,
                    'msg' => __('There was a problem with the subscription'),
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 'ERROR',
                    'msg' => __('Something went wrong with the subscription'),
                ];
            }
        }
        return $resultJsonFactory->create()->setData($response);
    }
}
