<?php

namespace Omnyfy\Vendor\Plugin;

class SendReviewProductEmail
{
    /**
     * @var \Omnyfy\Vendor\Helper\Data
     */
    private $data;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    public function __construct(
        \Omnyfy\Vendor\Helper\Data $data,
        \Magento\Catalog\Model\ProductRepository $productRepository
    )
    {
        $this->data = $data;
        $this->productRepository = $productRepository;
    }

    public function beforeExecute(\Magento\Review\Controller\Product\Post $subject)
    {
        $params = $subject->getRequest()->getParams();
        if (isset($params['id'])) {
            $product = $this->productRepository->getById($params['id']);
            try {
                $this->data->sendReviewProductEmailToMo($product->getName(), $params['nickname'], $params['title'], $params['detail']);
            } catch (\Exception $e) {
                throw new \Exception(__('Some thing wen\'t wrong send email review product'));
            }
        }
    }
}