<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 5/11/2018
 * Time: 9:16 AM
 */

namespace Omnyfy\Enquiry\Controller\Adminhtml\Messages;


class Email extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var string
     */
    protected $temp_id;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->_scopeConfig = $context;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
    }

    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Return template id according to store
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return 1;
    }


    public function execute()
    {
        $receiverInfo = array (
            'email' => 'receiver@mailinator.com',
            'name' => 'Reciver Name'
        );

        $senderInfo = array (
            'email' => 'sender@mailinator.com',
            'name' => 'Sender Name'
        );

        $emailTemplateVariables = array (

        );

        $this->temp_id = $this->getTemplateId('enquiry/message_customer/template');
        $this->inlineTranslation->suspend();
        $transport = $this->_transportBuilder->setTemplateIdentifier($this->temp_id)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo("receiver@mailinator.com","Test")
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
        echo "Send Email";
    }
}
