<?php

namespace Omnyfy\VendorReview\Controller\Product;

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
        $productId = (int)$this->getRequest()->getParam('id');

        $product = $this->helper->getProductById($productId);

        $output = $this->layoutFactory->create()
            ->createBlock(
                \Magento\Review\Block\Form::class,
                '',
                [
                    'data' =>  [
                        'jsLayout' => [
                            'components' => [
                                'review-form' => [
                                    'component' => 'Magento_Review/js/view/review'
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->toHtml();

        $response = [
            'product_name' => $product->getName(),
            'product_image' => $this->helper->getImageProduct($productId),
            'product_review_form' => $output
        ];

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $resultJson->setData($response);

        return $resultJson;
    }
}