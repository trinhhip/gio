<?php


namespace Omnyfy\Enquiry\Model;

use Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface;

class EnquiryMessages extends \Magento\Framework\Model\AbstractModel implements EnquiryMessagesInterface
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Omnyfy\Enquiry\Model\ResourceModel\EnquiryMessages');
    }

    /**
     * Get enquiry_messages_id
     * @return string
     */
    public function getEnquiryMessagesId()
    {
        return $this->getData(self::ENQUIRY_MESSAGES_ID);
    }

    /**
     * Set enquiry_messages_id
     * @param string $enquiryMessagesId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setEnquiryMessagesId($enquiryMessagesId)
    {
        return $this->setData(self::ENQUIRY_MESSAGES_ID, $enquiryMessagesId);
    }

    /**
     * Get from_id
     * @return string
     */
    public function getFromId()
    {
        return $this->getData(self::FROM_ID);
    }

    /**
     * Set from_id
     * @param string $fromId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setFromId($fromId)
    {
        return $this->setData(self::FROM_ID, $fromId);
    }

    /**
     * Get from_type
     * @return string
     */
    public function getFromType()
    {
        return $this->getData(self::FROM_TYPE);
    }

    /**
     * Set from_type
     * @param string $fromType
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setFromType($fromType)
    {
        return $this->setData(self::FROM_TYPE, $fromType);
    }

    /**
     * Get to_id
     * @return string
     */
    public function getToId()
    {
        return $this->getData(self::TO_ID);
    }

    /**
     * Set to_id
     * @param string $toId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setToId($toId)
    {
        return $this->setData(self::TO_ID, $toId);
    }

    /**
     * Get to_type
     * @return string
     */
    public function getToType()
    {
        return $this->getData(self::TO_TYPE);
    }

    /**
     * Set to_type
     * @param string $toType
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setToType($toType)
    {
        return $this->setData(self::TO_TYPE, $toType);
    }

    /**
     * Get send_time
     * @return string
     */
    public function getSendTime()
    {
        return $this->getData(self::SEND_TIME);
    }

    /**
     * Set send_time
     * @param string $sendTime
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setSendTime($sendTime)
    {
        return $this->setData(self::SEND_TIME, $sendTime);
    }

    /**
     * Get message
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Set message
     * @param string $message
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * Get is_notify_customer
     * @return string
     */
    public function getIsNotifyCustomer()
    {
        return $this->getData(self::IS_NOTIFY_CUSTOMER);
    }

    /**
     * Set is_notify_customer
     * @param string $isNotifyCustomer
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setIsNotifyCustomer($isNotifyCustomer)
    {
        return $this->setData(self::IS_NOTIFY_CUSTOMER, $isNotifyCustomer);
    }

    /**
     * Get is_visible_frontend
     * @return string
     */
    public function getIsVisibleFrontend()
    {
        return $this->getData(self::IS_VISIBLE_FRONTEND);
    }

    /**
     * Set is_visible_frontend
     * @param string $isVisibleFrontend
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setIsVisibleFrontend($isVisibleFrontend)
    {
        return $this->setData(self::IS_VISIBLE_FRONTEND, $isVisibleFrontend);
    }

    /**
     * Get status
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
