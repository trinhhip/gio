<?php

namespace OmnyfyCustomzation\PriceToQuote\Helper;

use Amasty\HidePrice\Helper\Data as AmastyHelper;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\State;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const IS_ENABLE = 'price_to_quote/general/enabled';
    const EMAIL = 'price_to_quote/general/email';
    const EMAIL_SENDER = 'price_to_quote/general/email_sender';
    const LABEL = 'price_to_quote/general/label';
    const EMAIL_TEMPLATE_ID = 'price_to_quote/general/email_template';

    /**
     * @var AmastyHelper
     */
    public $amastyHelper;
    /**
     * @var State
     */
    public $state;

    /**
     * Data constructor.
     * @param Context $context
     * @param State $state
     * @param AmastyHelper $amastyHelper
     */
    public function __construct(
        Context $context,
        State $state,
        AmastyHelper $amastyHelper
    )
    {
        parent::__construct($context);
        $this->amastyHelper = $amastyHelper;
        $this->state = $state;
    }

    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue(self::IS_ENABLE);
    }

    public function getEmailTemplateId()
    {
        return $this->scopeConfig->getValue(self::EMAIL_TEMPLATE_ID);
    }

    public function getEmailConfig()
    {
        return $this->scopeConfig->getValue(self::EMAIL);
    }

    public function getLabel()
    {
        return $this->scopeConfig->getValue(self::LABEL);
    }

    public function getEmailSender()
    {
        return $this->scopeConfig->getValue(self::EMAIL_SENDER);
    }

    public function isPriceToQuote($product)
    {
        return !$this->isAmastyApply($product)
            && $this->isModuleEnabled()
            && $product->getPriceToBeQuoted()
            && $this->state->getAreaCode() == Area::AREA_FRONTEND;
    }

    public function isAmastyApply($product)
    {
        return $this->amastyHelper->isApplied($product);
    }

    public function getNewPriceHtmlBox($priceCode)
    {
        $priceHtml = '';
        if (in_array($priceCode, $this->getAllowPriceCode())) {
            $priceHtml = __('<div class="price-box price-to-quote"><span class="price-container"> <span class="price-wrapper"><span class="price price-quote">%1</span></span> </span></div>', $this->getLabel());
        }
        return $priceHtml;
    }

    public function getEmailUsBox($productId)
    {
        $priceToQuoteUrl = $this->_urlBuilder->getUrl('catalog/product/quote', ['id' => $productId]);
        $buttonLabel = __('Email Us');
        return __('<a href="%1" target="_blank" class="action primary email-us">%2</a>', $priceToQuoteUrl, $buttonLabel);
    }

    public function getAllowPriceCode()
    {
        return [
            'wishlist_configured_price',
            'final_price'
        ];
    }
}
