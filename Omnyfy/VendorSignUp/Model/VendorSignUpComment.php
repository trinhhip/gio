<?php
namespace Omnyfy\VendorSignUp\Model;

use \Magento\Config\Model\Config\CommentInterface;

class VendorSignUpComment implements CommentInterface
{
    protected $_backendUrl;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl
    )
    {
        $this->_backendUrl = $backendUrl;
    }

    public function getCommentText($elementValue)
    {
        $__link = $this->_backendUrl->getUrl("admin/email_template/index");
        return 'Select which e-mail template is used to notify the Marketplace Owner of a new Vendor Sign Up. <a href="'.$__link.'">Click here to create a new one.</a>';
    }
}