<?php

namespace OmnyfyCustomzation\VendorSignUp\Controller\Index;

/**
 * Class Save
 *
 * @package OmnyfyCustomzation\VendorSignUp\Controller\Index
 */
class Save extends \Omnyfy\VendorSignUp\Controller\Index\Save
{
    public function processResult($backUrl = null, $result = null)
    {
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('***********');
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('***********' . $backUrl);
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('***********', $result);

        $resultJson = $this->resultJsonFactory->create($result);

        if (empty($backUrl)) {
            $backUrl = '/';
        }

        if (!empty($result) && isset($result['message'])) {
            if (isset($result['error'])) {
                $this->messageManager->addErrorMessage(__($result['message']));
                $resultJson->setData([
                    'success' => $result['error'],
                    'message' => __($result['message']),
                    'redirect' => $backUrl
                ]);
            } else {
                $this->messageManager->addSuccessMessage(__($result['message']));
                if (isset($result['success'])) {
                    $resultJson->setData([
                        'success' => $result['success'],
                        'message' => __($result['message']),
                        'redirect' => $backUrl
                    ]);
                } else {
                    $this->messageManager->addSuccessMessage(__($result['message']));
                    $resultJson->setData([
                        'success' => false,
                        'message' => __($result['message']),
                        'redirect' => $backUrl
                    ]);
                }

            }
        }
        return $resultJson;
    }
}