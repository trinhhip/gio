<?php

namespace Omnyfy\VendorReview\Controller\Vendor;

use Magento\Framework\Controller\ResultFactory;

class Review extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
    */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    protected $helper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Omnyfy\VendorReview\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->layoutFactory = $layoutFactory;
        $this->helper = $helper;
    }


    public function execute()
    {
        $vendorId = (int)$this->getRequest()->getParam('id');
        $vendor = $this->helper->getVendorById($vendorId);

        // TODO: what if there is no vendor?

        $output = $this->layoutFactory->create()
            ->createBlock(
                \Omnyfy\VendorReview\Block\Form::class,
                'vendor_review_block_form',
                [
                    'data' =>  [
                        'jsLayout' => [
                            'components' => [
                                'review-form' => [
                                    'component' => 'Omnyfy_VendorReview/js/view/review'
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->setTemplate('Omnyfy_VendorReview::merchant/reviews/form.phtml')
            ->toHtml();

        $response = [
            'vendor_name' => $vendor->getName(),
            'vendor_logo' => $this->helper->getImageVendor($vendorId),
            'vendor_review_form' => $output
        ];

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $resultJson->setData($response);

        return $resultJson;
    }
}
