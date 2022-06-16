<?php


namespace OmnyfyCustomzation\PriceToQuote\Controller\Product;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\Store;
use OmnyfyCustomzation\PriceToQuote\Helper\Data;
use OmnyfyCustomzation\PriceToQuote\Model\ProductToQuoteFactory;
use OmnyfyCustomzation\PriceToQuote\Model\ResourceModel\ProductToQuote as ProductToQuoteResource;
use function strpos;

class QuotePost extends Action
{
    const IS_SENT_EMAIL = 1;
    /**
     * @var Data
     */
    public $helperData;
    /**
     * @var TransportBuilder
     */
    public $transportBuilder;
    /**
     * @var StateInterface
     */
    public $inlineTranslation;
    /**
     * @var ProductToQuoteFactory
     */
    public $productToQuoteFactory;
    /**
     * @var ProductToQuoteResource
     */
    public $productToQuoteResource;

    public function __construct(
        Data $helperData,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ProductToQuoteFactory $productToQuoteFactory,
        ProductToQuoteResource $productToQuoteResource,
        Context $context
    )
    {
        $this->helperData = $helperData;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->productToQuoteFactory = $productToQuoteFactory;
        $this->productToQuoteResource = $productToQuoteResource;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $productId = $this->getRequest()->getParam('product_id');
        try {
            $postParams = $this->validatedParams();
            $this->sendMail($postParams);
            $data = [
                'customer_name' => $postParams['name'],
                'customer_email' => $postParams['email'],
                'inquiry' => $postParams['inquiry'],
                'product_id' => $productId,
                'product_sku' => $postParams['product_sku'],
                'is_sent_email' => self::IS_SENT_EMAIL
            ];
            $productToQuote = $this->productToQuoteFactory->create();
            $productToQuote->setData($data);
            $this->productToQuoteResource->save($productToQuote);

            $this->messageManager->addSuccessMessage(
                __('Your request has been submitted. We will get back to your inquiry as soon as we can.')
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
        }
        return $this->resultRedirectFactory->create()->setPath('catalog/product/quote', ['id' => $productId, 'submitted' => 1]);
    }

    /**
     * @return array
     * @throws Exception
     */
    private function validatedParams()
    {
        $request = $this->getRequest();
        if (trim($request->getParam('name')) === '') {
            throw new LocalizedException(__('Name is missing'));
        }
        if (trim($request->getParam('inquiry')) === '') {
            throw new LocalizedException(__('Inquiry is missing'));
        }
        if (false === strpos($request->getParam('email'), '@')) {
            throw new LocalizedException(__('Invalid email address'));
        }

        return $request->getParams();
    }

    protected function sendMail($postParams)
    {
        $dataObject = new DataObject();
        $dataObject->setData($postParams);
        $templateId = $this->helperData->getEmailTemplateId();
        $mailTo = $this->helperData->getEmailConfig();
        $mailSender = $this->helperData->getEmailSender();
        $sender = [
            'name' => $postParams['name'],
            'email' => $mailSender,
        ];
        $this->inlineTranslation->suspend();

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => Store::DEFAULT_STORE_ID
                ])
            ->setTemplateVars(['data' => $dataObject])
            ->setFrom($sender)
            ->addTo($mailTo)
            ->setReplyTo($postParams['email'])
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }
}
