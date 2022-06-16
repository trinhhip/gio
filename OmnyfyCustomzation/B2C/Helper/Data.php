<?php


namespace OmnyfyCustomzation\B2C\Helper;


use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as AuthContext;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use OmnyfyCustomzation\B2C\Block\Retail\Price;
use OmnyfyCustomzation\B2C\Model\Product\Attribute\Source\ForRetail;

class Data extends AbstractHelper
{
    const XML_RETAIL_CUSTOMER_GROUP = 'b2c/general/customer_retail_group';
    const XML_TRADE_CUSTOMER_GROUP = 'b2c/general/customer_trade_group';
    const XML_ADD_TO_CART_LABEL = 'b2c/general/addtocart_label';
    const XML_RETAIL_CREATE_SUCCESS = 'b2c/general/retail_create_success_message';
    const XML_TO_TRADE_URL = 'amasty_hide_price/frontend/link';
    const XML_ALLOW_COUNTRIES = 'b2c/shipping/allow_countries';
    const XML_NOTIFICATION_MESSAGE = 'b2c/shipping/notification_message';

    /**
     * @var Template
     */
    public $template;
    /**
     * @var PriceHelper
     */
    public $priceHelper;
    /**
     * @var ResourceConnection
     */
    public $resourceConnection;
    /**
     * @var AuthContext
     */
    public $authContext;
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var Http
     */
    public $request;

    public function __construct(
        Context $context,
        Template $template,
        PriceHelper $priceHelper,
        ResourceConnection $resourceConnection,
        AuthContext $authContext,
        StoreManagerInterface $storeManager,
        Http $request
    )
    {
        parent::__construct($context);
        $this->template = $template;
        $this->priceHelper = $priceHelper;
        $this->resourceConnection = $resourceConnection;
        $this->authContext = $authContext;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    public function isLoggedIn()
    {
        return $this->authContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    public function getCurrentCustomerGroup()
    {
        return $this->authContext->getValue(CustomerContext::CONTEXT_GROUP);
    }

    public function isRetailBuyer()
    {
        return $this->isLoggedIn() ? ($this->getDefaultCustomerGroup() == $this->getCurrentCustomerGroup()) : true;
    }

    public function getDefaultCustomerGroup()
    {
        return $this->scopeConfig->getValue(self::XML_RETAIL_CUSTOMER_GROUP, ScopeInterface::SCOPE_STORE);
    }

    public function getTradeCustomerGroup()
    {
        return $this->scopeConfig->getValue(self::XML_TRADE_CUSTOMER_GROUP, ScopeInterface::SCOPE_STORE);
    }

    public function getAddToCartLabel()
    {
        return $this->scopeConfig->getValue(self::XML_ADD_TO_CART_LABEL, ScopeInterface::SCOPE_STORE);
    }

    public function getRetailCreateSuccessMessage()
    {
        return $this->scopeConfig->getValue(self::XML_RETAIL_CREATE_SUCCESS, ScopeInterface::SCOPE_STORE);
    }

    public function getAllowCountries()
    {
        return $this->scopeConfig->getValue(self::XML_ALLOW_COUNTRIES, ScopeInterface::SCOPE_STORE);
    }

    public function getNotificationMessage()
    {
        return $this->scopeConfig->getValue(self::XML_NOTIFICATION_MESSAGE, ScopeInterface::SCOPE_STORE);
    }

    public function getFormatPrice($price)
    {
        return $this->priceHelper->currency($price);
    }

    public function getRetailPriceHtml($product)
    {
        return $this->template->getLayout()->createBlock(
            Price::class,
            null,
            [
                'data' => [
                    'product' => $product,
                    'add_to_cart_label' => $this->getAddToCartLabel(),
                    'is_show_retail_price' => $this->isShowRetailPrice($product)
                ]
            ]
        )->setTemplate("OmnyfyCustomzation_B2C::form/retail/price.phtml")->toHtml();
    }

    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    public function isProductPage()
    {
        return $this->request->getFullActionName() == 'catalog_product_view';
    }


    public function isWishListConfigPage()
    {
        return $this->request->getFullActionName() == 'wishlist_index_configure';
    }

    public function getToTradeUrl()
    {
        return $this->scopeConfig->getValue(self::XML_TO_TRADE_URL, ScopeInterface::SCOPE_STORE);
    }

    public function isShowRetailPrice($product)
    {
        return $this->isProductRetail($product) && $this->isRetailBuyer() && $this->isNotAllowPages();
    }

    public function isNotAllowPages()
    {
        $allowPage = [
            'catalog_product_view',
            'wishlist_index_configure',
            'wishlist_index_index'
        ];
        return !in_array($this->request->getFullActionName(), $allowPage);
    }

    public function getRetailPrice($product)
    {
        $price = $product->getFinalPrice();
        if ($product->getTypeId() == 'configurable') {
            $price = $this->getMinConfigurablePrice($product);
        }
        return $this->getFormatPrice($price);
    }

    protected function getMinConfigurablePrice($product)
    {
        $childProducts = $product->getTypeInstance()->getUsedProducts($product);
        $price = isset($childProducts[0]) ? $childProducts[0]->getFinalPrice() : 0;
        foreach ($childProducts as $childProduct) {
            if ($childProduct->getFinalPrice() < $price) {
                $price = $childProduct->getFinalPrice();
            }
        }
        return $price;
    }

    public function isProductRetail($product)
    {
        return $product->getForRetail() == ForRetail::YES && !$product->getPriceToBeQuoted();
    }
}
