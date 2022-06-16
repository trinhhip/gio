<?php


namespace Omnyfy\Vendor\Block\Vendor\View;


class BookingLink extends \Omnyfy\Core\Block\Element\Html\Link\PageSectionLink {

    /**
     * @var \Omnyfy\Vendor\Block\Vendor\View\Policy
     */
    private $_vendorView;
    private $location;

    /**
     * BookingLink constructor.
     * @param \Omnyfy\Vendor\Helper\Location $location
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
       \Omnyfy\Vendor\Helper\Location $location,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->location = $location;
    }



    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }
        if ($this->location->isBookingModuleOutputEnabled()) {
            return '<li><a ' . $this->getLinkAttributes() . ' >' . $this->escapeHtml($this->getLabel()) . '</a></li>';
        } else {
            return '';
        }
    }
}
