<?php
declare(strict_types=1);

namespace Omnyfy\VendorSignUp\Observer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\RequestHandlerInterface;
use Magento\Framework\App\Response\RedirectInterface;

class RecapchaSignupFormObserver implements ObserverInterface
{
    protected $redirect;
    private $url;
    private $isCaptchaEnabled;
    private $requestHandler;

    public function __construct(
        UrlInterface $url,
        IsCaptchaEnabledInterface $isCaptchaEnabled,
        RequestHandlerInterface $requestHandler,
        RedirectInterface $redirect
    ) {
        $this->url = $url;
        $this->isCaptchaEnabled = $isCaptchaEnabled;
        $this->requestHandler = $requestHandler;
        $this->redirect = $redirect;
    }

    public function execute(Observer $observer): void
    {
        $key = 'vendor_signup_form';
        if ($this->isCaptchaEnabled->isCaptchaEnabledFor($key)) {
            /** @var Action $controller */
            $controller = $observer->getControllerAction();
            $request = $controller->getRequest();
            $response = $controller->getResponse();
            $redirectOnFailureUrl = $this->redirect->getRedirectUrl();
            $this->requestHandler->execute($key, $request, $response, $redirectOnFailureUrl);
        }
    }
}