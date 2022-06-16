<?php


namespace OmnyfyCustomzation\Navigation\Block\Navigation\Widget;


use Amasty\Shopby\Helper\Data;
use Magento\Directory\Model\Currency;
use Magento\Framework\View\Element\Template;

class FromTo extends \Amasty\Shopby\Block\Navigation\Widget\FromTo
{
    /**
     * @var string
     */
    protected $_template = 'OmnyfyCustomzation_Navigation::layer/widget/fromto.phtml';

    /**
     * @var Currency
     */
    public $currency;

    public function __construct(
        Template\Context $context,
        Data $helper,
        Currency $currency,
        array $data = []
    )
    {
        $this->currency =$currency;
        parent::__construct($context, $helper, $data);
    }
    public function getCurrencySymbol(){
        return $this->currency->getCurrencySymbol();
    }
}
