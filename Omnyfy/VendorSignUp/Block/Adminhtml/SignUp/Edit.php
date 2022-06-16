<?php

namespace Omnyfy\VendorSignUp\Block\Adminhtml\SignUp;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Continue" button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_signUp';
        $this->_blockGroup = 'Omnyfy_VendorSignUp';
        parent::_construct();
        $this->updateButton('back', 'onclick', 'setLocation(\'' . $this->getBackUrl() . '\')');
        $this->removeButton('delete');
        $this->removeButton('reset');
    }

    public function getBackUrl()
    {
        return $this->getUrl(
            '*/*/view',
            ['id' => $this->getRequest()->getParam('id')]
        );
    }
}
