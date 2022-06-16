<?php


namespace Omnyfy\Order\Helper;


use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

class Shipping extends AbstractHelper
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Shipping constructor.
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    )
    {
        parent::__construct($context);
        $this->registry = $registry;
    }

    public function getTemplate(): string
    {
        /* @var Order $order*/
        $order = $this->registry->registry('current_order');
        $shippingMethodSelected = json_decode($order->getShippingMethod(), true);
        if(!empty($shippingMethodSelected)){
            if($this->getIsEasyShip($shippingMethodSelected)){
                return "Omnyfy_Easyship::order/view/info.phtml";
            } else if($this->getIsSherpaShip($shippingMethodSelected)){
                return "Omnyfy_Sherpa::order/view/info.phtml";
            }elseif($this->getIsHdsDelivery($shippingMethodSelected)){
                return "Omnyfy_Hds::order/view/info.phtml";
            }
        }
        return "Omnyfy_Vendor::order/view/info.phtml";
    }


    protected function getIsEasyShip($shippingMethodSelected): bool
    {
        foreach($shippingMethodSelected as $value){
            if(preg_match("/easyship_easyship/i", $value)){
                return true;
            }
        }
        return false;
    }

    protected function getIsSherpaShip($shippingMethodSelected): bool
    {
        foreach($shippingMethodSelected as $value){
            if(preg_match("/sherpa_sherpa/", $value)){
                return true;
            }
        }
        return false;
    }

    protected function getIsHdsDelivery($shippingMethodSelected): bool
    {
        foreach($shippingMethodSelected as $value){
            if(preg_match("/hds_hds/", $value)){
                return true;
            }
        }
        return false;
    }
}
