<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.25
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Block\Rma\View;

use Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface;
use Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface;
use Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface;
use Mirasvit\Rma\Service\Config\RmaRequirementConfig;
use Mirasvit\Rma\Model\ItemFactory;
use Magento\Framework\Registry;
use Magento\Catalog\Helper\Image;
use Mirasvit\Rma\Helper\Item\Html;
use Mirasvit\Rma\Api\Service\Item\ItemManagementInterface;
use Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface;
use Magento\Catalog\Model\ProductFactory;
use Mirasvit\Rma\Api\Repository\RmaRepositoryInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Items extends \Magento\Framework\View\Element\Template
{
    private $order;

    private $rmaSearchManagement;

    private $offlineOrderRepository;

    private $config;

    private $itemFactory;

    private $registry;

    private $imageHelper;

    private $rmaItemHtml;

    private $itemManagement;

    private $itemProductManagement;

    private $productFactory;

    private $context;

    private $rmaManagement;

    private $rmaRepository;

    public function __construct(
        SearchInterface $rmaSearchManagement,
        RmaManagementInterface $rmaManagement,
        OfflineOrderRepositoryInterface $offlineOrderRepository,
        RmaRequirementConfig $config,
        ItemFactory $itemFactory,
        Registry $registry,
        Image $imageHelper,
        Html $rmaItemHtml,
        ItemManagementInterface $itemManagement,
        ProductInterface $itemProductManagement,
        ProductFactory $productFactory,
        RmaRepositoryInterface $rmaRepository,
        Context $context,
        array $data = []
    ) {
        $this->rmaSearchManagement    = $rmaSearchManagement;
        $this->rmaManagement          = $rmaManagement;
        $this->offlineOrderRepository = $offlineOrderRepository;
        $this->config                 = $config;
        $this->itemFactory            = $itemFactory;
        $this->registry               = $registry;
        $this->imageHelper            = $imageHelper;
        $this->rmaItemHtml            = $rmaItemHtml;
        $this->itemManagement         = $itemManagement;
        $this->itemProductManagement  = $itemProductManagement;
        $this->productFactory         = $productFactory;
        $this->rmaRepository          = $rmaRepository;
        $this->context                = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order[]|\Mirasvit\Rma\Api\Data\OfflineOrderInterface[]
     */
    public function getOrders()
    {
        return $this->rmaManagement->getOrders($this->getRma());
    }

    /**
     * @param \Magento\Sales\Model\Order|\Mirasvit\Rma\Api\Data\OfflineOrderInterface $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order|\Mirasvit\Rma\Api\Data\OfflineOrderInterface
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \Magento\Sales\Model\Order|\Mirasvit\Rma\Api\Data\OfflineOrderInterface $order
     * @return string
     */
    public function displayOrder($order)
    {
        if ($order->getIsOffline()) {
            $block = $this->getLayout()->createBlock(\Mirasvit\Rma\Block\Rma\View\Email\OfflineOrder::class);
        } else {
            $block = $this->getLayout()->createBlock(\Mirasvit\Rma\Block\Rma\View\Email\Order::class);
        }

        $block->setOrder($order);
        $block->setArea($this->getArea());
        $block->setRma($this->getRma());

        return $block->toHtml();
    }

    /**
     * @return bool
     */
    public function isReasonAllowed()
    {
        return $this->config->isCustomerReasonRequired();
    }

    /**
     * @return bool
     */
    public function isConditionAllowed()
    {
        return $this->config->isCustomerConditionRequired();
    }

    /**
     * @return bool
     */
    public function isResolutionAllowed()
    {
        return $this->config->isCustomerResolutionRequired();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        if ($this->getData('rma') && $this->getData('rma')->getId()) {

            return $this->rmaRepository->get($this->getData('rma')->getId());
        }

        return $this->registry->registry('current_rma');
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getItems()
    {
        $items = $this->rmaSearchManagement->getRequestedItems($this->getRma());
        $currentOrder = $this->getOrder();

        $result = [];
        foreach ($items as $item) {
            $itemOrderId = $this->getOrderId($item);
            if ($itemOrderId == $currentOrder->getId()) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\OfflineItemInterface[]
     */
    public function getOfflineItems()
    {
        $items = $this->rmaSearchManagement->getRequestedOfflineItems($this->getRma());
        $currentOrder = $this->getOrder();

        $result = [];
        foreach ($items as $item) {
            $itemOrder = $this->getOfflineOrder($item);
            if ($itemOrder->getOfflineOrderId() == $currentOrder->getOfflineOrderId()) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @param string                               $imageId
     * @param array                                $attributes
     * @return \Magento\Catalog\Helper\Image
     */
    public function initImage($item, $imageId, $attributes = [])
    {
        return $this->itemProductManagement->getImage($item, $imageId, $attributes);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct($item)
    {
        return $this->itemProductManagement->getProduct($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getOrderItemLabel(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->rmaItemHtml->getItemLabel($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getOrderItemSku(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->rmaItemHtml->getItemSku($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getOrderItemOrderIncrement(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->rmaItemHtml->getItemOrderIncrement($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getItemWeight(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $weight = $this->getProduct($item)->getWeight() * $item->getQtyRequested();

        if (!$weight) {
            $weight = '--';
        }

        return $weight;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface|\Mirasvit\Rma\Api\Data\OfflineItemInterface $item
     * @return string
     */
    public function getReasonName($item)
    {
        return $this->itemManagement->getReasonName($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface|\Mirasvit\Rma\Api\Data\OfflineItemInterface $item
     * @return string
     */
    public function getConditionName($item)
    {
        return $this->itemManagement->getConditionName($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface|\Mirasvit\Rma\Api\Data\OfflineItemInterface $item
     * @return string
     */
    public function getResolutionName($item)
    {
        return $this->itemManagement->getResolutionName($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface|\Mirasvit\Rma\Api\Data\OfflineItemInterface $item
     * @return \Mirasvit\Rma\Api\Data\OfflineOrderInterface
     */
    public function getOfflineOrder($item)
    {
        return $this->offlineOrderRepository->get($item->getOfflineOrderId());
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return int
     */
    public function getOrderId($item)
    {
        $orderItem = $this->itemManagement->getOrderItem($item);
        return $orderItem->getOrderId();
    }
}
