<?php

namespace OmnyfyCustomzation\Catalog\Block\Product;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

/**
 * Product Review Tab
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since      100.0.2
 */
class ShippingTab extends Template implements IdentityInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);

        $this->setTabTitle();
    }


    /**
     * Get current product
     *
     * @return null|int
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get Shipping and returns
     *
     * @return string|null
     */
    public function getShippingAndReturns()
    {
        return ($product = $this->getProduct()) ? $product->getShippingAndReturns() : null;
    }

    /**
     * Set tab title
     *
     * @return void
     */
    public function setTabTitle()
    {
        $this->setTitle(__('Shipping & Returns'));
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return ['shipping_tab'];
    }
}
