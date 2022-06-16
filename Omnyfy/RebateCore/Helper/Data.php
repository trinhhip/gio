<?php

namespace Omnyfy\RebateCore\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Omnyfy\RebateCore\Model\Mail\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Area;
use Zend_Db_Expr;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;

/**
 * Class Data
 * @package Omnyfy\RebateCore\Helper
 */
class Data extends AbstractHelper
{
    /**
     *
     */
    const TEMPLATE_EMAIL_VENDORS = 'omnyfy_rebate_core/admin_email/template';
    /**
     *
     */
    const TEMPLATE_EMAIL_MO = 'omnyfy_rebate_core/mo_email/template';
    /**
     *
     */
    const TEMPLATE_EMAIL_INVOICE = 'omnyfy_rebate_core/invoice_email/template';

    /**
     *
     */
    const ENABLE_XML_PATH = 'omnyfy_rebate_core/general/enable';

    /**
     *
     */
    const PREFIX_INVOICE_XML_PATH = 'omnyfy_rebate_core/general/prefix_invoice';

    /**
     *
     */
    const PAYMENT_DETAIL_XML_PATH = 'omnyfy_rebate_core/payment_detail/payment_detail';

    /**
     *
     */
    const PAYMENT_TERM_XML_PATH = 'omnyfy_rebate_core/general/payment_term';

    /**
     *
     */
    const SUPPORT_NAME_XML_PATH = 'trans_email/ident_support/name';
    /**
     *
     */
    const SUPPORT_EMAIL_XML_PATH = 'trans_email/ident_support/email';
    /**
     *
     */
    const OWNER_NAME_XML_PATH = 'trans_email/ident_general/name';
    /**
     *
     */
    const OWNER_EMAIL_XML_PATH = 'trans_email/ident_general/email';

    /**
     *
     */
    const STORE_NAME_XML_PATH = 'general/store_information/name';

    /**
     *
     */
    const STORE_ADDRESS_XML_PATH = 'general/store_information/street_line1';

    /**
     *
     */
    const VAT_NUMBER_XML_PATH = 'general/store_information/merchant_vat_number';
    /**
     * @var StateInterface
     */
    private $inlineTranslation;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeConfig;

    /**
     * @var CurrencyFactory
     */
    private $currencyCode;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * Data constructor.
     * @param StoreManagerInterface $storeConfig
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        VendorRepositoryInterface $vendorRepository,
        Context $context
    )
    {
        $this->storeConfig = $storeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->vendorRepository = $vendorRepository;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function isEnable()
    {
        return $this->getConfig($this::ENABLE_XML_PATH);
    }

    /**
     * @return mixed
     */
    public function getConfig($config)
    {
        $storeScope = ScopeInterface::SCOPE_STORES;
        return $this->scopeConfig->getValue($config, $storeScope);
    }
    
    /**
     * @return mixed
     */
    public function getPreFixInvoice()
    {
        return $this->getConfig($this::PREFIX_INVOICE_XML_PATH);
    }
    /**
     * @return mixed
     */
    public function getPaymentDetail()
    {
        return $this->getConfig($this::PAYMENT_DETAIL_XML_PATH);
    }

    /**
     * @return mixed
     */
    public function getPaymentTerm()
    {
        return $this->getConfig($this::PAYMENT_TERM_XML_PATH);
    }

    /**
     * @return mixed
     */
    public function getStoreName()
    {
        return $this->getConfig($this::STORE_NAME_XML_PATH);
    }

    /**
     * @return mixed
     */
    public function getStoreAddress()
    {
        return $this->getConfig($this::STORE_ADDRESS_XML_PATH);
    }

    /**
     * @return mixed
     */
    public function getStoreVAT()
    {
        return $this->getConfig($this::VAT_NUMBER_XML_PATH);
    }

    /**
     * @return mixed
     */
    public function getStoreSupportName()
    {
        return $this->getConfig($this::SUPPORT_NAME_XML_PATH);
    }

    /**
     * @return mixed
     */
    public function getStoreSupportEmail()
    {
        return $this->getConfig($this::SUPPORT_EMAIL_XML_PATH);
    }

    /**
     * @return mixed
     */
    public function getStoreOwnerName()
    {
        return $this->getConfig($this::OWNER_NAME_XML_PATH);
    }

    /**
     * @return mixed
     */
    public function getStoreOwnerEmail()
    {
        return $this->getConfig($this::OWNER_EMAIL_XML_PATH);
    }


    /**
     * @param $templateId
     * @param $vars
     * @param $sendEmail
     * @param $area
     */
    public function sendEmail($templateId, $vars, $sendEmail, $area = Area::AREA_FRONTEND)
    {
        $sender = [
            "email" => $this->getStoreSupportEmail(),
            "name" => $this->getStoreSupportName()
        ];
        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateVars($vars)
            ->setTemplateOptions([
                'area' => $area,
                'store' => $this->storeConfig->getStore()->getId()
            ])
            ->setFrom($sender)
            ->addTo($sendEmail["email"], $sendEmail["name"]);

        $transport->getTransport()->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * @param $templateId
     * @param $vars
     * @param $sendEmail
     * @param $area
     */
    public function sendEmailInvoice($pdfFile, $pdfName, $vars, $sendEmail, $area = Area::AREA_FRONTEND)
    {
        $sender = [
            "email" => $this->getStoreSupportEmail(),
            "name" => $this->getStoreName()
        ];
        $templateId = $this->getConfig($this::TEMPLATE_EMAIL_INVOICE);
        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateVars($vars)
            ->setTemplateOptions([
                'area' => $area,
                'store' => $this->storeConfig->getStore()->getId()
            ])
            ->setFrom($sender)
            ->addTo($sendEmail["email"], $sendEmail["name"])
            ->addAttachment($pdfFile, $pdfName);

        $transport->getTransport()->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * @param $vars
     * @param $sendEmail
     */
    public function sendEmailVendorRequest($vars, $sendEmail)
    {
        $templateId = $this->getConfig($this::TEMPLATE_EMAIL_VENDORS);
        $this->sendEmail($templateId, $vars, $sendEmail);
    }

    /**
     * @param $vars
     */
    public function sendEmailMOSubmit($vars)
    {
        $templateId = $this->getConfig($this::TEMPLATE_EMAIL_MO);
        $sendEmail = [
            "email" => $this->getStoreOwnerEmail(),
            "name" => $this->getStoreOwnerName()
        ];
        $this->sendEmail($templateId, $vars, $sendEmail);
    }

    public function isWhosaleVendor($vendorId)
    {
        if ($vendorId) {
            $vendor = $this->vendorRepository->getById($vendorId);
            if ($vendor->getPayoutBasisType() == PayoutBasisType::WHOLESALE_VENDOR_VALUE) {
                return true;
            }
        }
        return false;
    }

}