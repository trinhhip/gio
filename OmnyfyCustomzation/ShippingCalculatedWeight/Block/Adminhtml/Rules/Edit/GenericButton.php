<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Block\Adminhtml\Rules\Edit;


use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

abstract class GenericButton
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(
        Context $context
    )
    {
        $this->context = $context;
    }

    public function getId()
    {
        try {
            return
                $this->context->getRequest()->getParam('entity_id');
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}