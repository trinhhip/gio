<?php
namespace Omnyfy\Easyship\Block\Adminhtml\Account;

class WebhookComment extends \Magento\Backend\Block\Template
{
    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'account/webhook_comment.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
    }

    public function getCommentText(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $comment = $this->_scopeConfig->getValue('carriers/easyship/webhook_comment', $storeScope);
        return $comment;
    }
}