<?php

namespace OmnyfyCustomzation\OrderNote\Plugin\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\Element\TemplateFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Store\Model\StoreManagerInterface;
use Omnyfy\Core\Helper\Queue;
use Omnyfy\Mcm\Model\ResourceModel\VendorOrder\CollectionFactory;
use Omnyfy\Vendor\Helper\Data;
use Omnyfy\Vendor\Model\Config;
use OmnyfyCustomzation\VendorConfirm\Helper\Data as HelperData;
use OmnyfyCustomzation\OrderNote\Helper\Data as OrderNoteHelper;

class SendVendorEmail extends \OmnyfyCustomzation\VendorConfirm\Command\SendVendorEmail
{
    /**
     * @var OrderNoteHelper
     */
    private $orderNoteHelper;

    public function __construct(
        State $state,
        Queue $queueHelper,
        Data $vendorHelper,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
        ResourceConnection $resource,
        Config $config,
        Renderer $addressRenderer,
        CollectionFactory $vendorOrderCollection,
        TemplateFactory $templateFactory,
        HelperData $helperData,
        StateInterface $inlineTranslation,
        OrderNoteHelper $orderNoteHelper,
        $name = null
    )
    {
        parent::__construct($state, $queueHelper, $vendorHelper, $transportBuilder, $storeManager, $orderRepository, $resource, $config, $addressRenderer, $vendorOrderCollection, $templateFactory, $helperData, $inlineTranslation, $name);
        $this->orderNoteHelper = $orderNoteHelper;
    }

    public function aroundGetItemTemplate(
        \OmnyfyCustomzation\VendorConfirm\Command\SendVendorEmail $subject,
        callable $proceed,
        $vendor,
        $order
    )
    {
        if(!$this->orderNoteHelper->isEnabled()) {
            return $proceed($vendor,$order);
        }
        return $this->templateFactory->create()->getLayout()->createBlock(
            \Magento\Framework\View\Element\Template::class,
            null,
            [
                'data' => [
                    'vendor' => $vendor,
                    'order' => $order,
                    'helper' => $this->helperData
                ]
            ]
        )->setTemplate("OmnyfyCustomzation_OrderNote::email/vendor/items.phtml")->toHtml();
    }

}
