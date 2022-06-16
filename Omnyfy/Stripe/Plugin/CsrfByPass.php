<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\Stripe\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Area;
use Magento\Framework\App\Request\CsrfValidator;

class CsrfByPass
{
    /** @const */
    const BY_PASS_URI = [
        '/omnyfy_stripe/subscription/created/', 
        '/omnyfy_stripe/invoice_payment/succeed/',
        '/omnyfy_stripe/invoice_payment/failed/',
        '/omnyfy_stripe/subscription/updated/',
        '/omnyfy_stripe/subscription/deleted/',
        '/omnyfy_stripe/webhooks'
    ];

    /**
     * Around validate
     *
     * @param CsrfValidator $validator
     * @param callable $proceed
     * @param RequestInterface $request
     * @param ActionInterface $action
     */
    public function aroundValidate(
        CsrfValidator $validator,
        callable $proceed,
        RequestInterface $request,
        ActionInterface $action
    )
    {
        try {
            /** @var State $appState */
            $appState = ObjectManager::getInstance()->get(State::class);
            $areaCode = $appState->getAreaCode();
        } catch (LocalizedException $exception) {
            $areaCode = null;
        }

        if ($request instanceof HttpRequest
            && in_array($areaCode, [Area::AREA_FRONTEND, Area::AREA_ADMINHTML], true)
        ) {
            if (!in_array($request->getPathInfo(), self::BY_PASS_URI)) {
                $proceed($request, $action);
            }
        }
    }
}