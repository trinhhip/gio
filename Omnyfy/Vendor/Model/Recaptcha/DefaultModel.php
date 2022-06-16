<?php
namespace Omnyfy\Vendor\Model\Recaptcha;

class DefaultModel extends \Magento\Captcha\Model\DefaultModel
{
    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param ResourceModel\LogFactory $resLogFactory
     * @param string $formId
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Captcha\Helper\Data $captchaData,
        \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory,
        $formId
    )
    {
        parent::__construct($session, $captchaData, $resLogFactory, $formId);
        $this->setDotNoiseLevel(1);
        $this->setLineNoiseLevel(1);
    }

}
