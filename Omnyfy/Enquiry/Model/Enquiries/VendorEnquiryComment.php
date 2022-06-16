<?php
namespace Omnyfy\Enquiry\Model\Enquiries;

use \Magento\Config\Model\Config\CommentInterface;

class VendorEnquiryComment implements CommentInterface
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
        return 'A notification email sent to vendors once receiving a new enquiry. <a href="'.$__link.'">Click here to create a new one.</a>';
    }
}