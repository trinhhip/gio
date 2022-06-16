<?php


namespace Omnyfy\Enquiry\Api\Data;

interface EnquiryMessagesInterface
{

    const IS_NOTIFY_CUSTOMER = 'is_notify_customer';
    const IS_VISIBLE_FRONTEND = 'is_visible_frontend';
    const FROM_ID = 'from_id';
    const SEND_TIME = 'send_time';
    const TO_TYPE = 'to_type';
    const FROM_TYPE = 'from_type';
    const MESSAGE = 'message';
    const TO_ID = 'to_id';
    const ENQUIRY_MESSAGES_ID = 'enquiry_messages_id';
    const STATUS = 'status';


    /**
     * Get enquiry_messages_id
     * @return string|null
     */
    public function getEnquiryMessagesId();

    /**
     * Set enquiry_messages_id
     * @param string $enquiryMessagesId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setEnquiryMessagesId($enquiryMessagesId);

    /**
     * Get from_id
     * @return string|null
     */
    public function getFromId();

    /**
     * Set from_id
     * @param string $fromId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setFromId($fromId);

    /**
     * Get from_type
     * @return string|null
     */
    public function getFromType();

    /**
     * Set from_type
     * @param string $fromType
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setFromType($fromType);

    /**
     * Get to_id
     * @return string|null
     */
    public function getToId();

    /**
     * Set to_id
     * @param string $toId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setToId($toId);

    /**
     * Get to_type
     * @return string|null
     */
    public function getToType();

    /**
     * Set to_type
     * @param string $toType
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setToType($toType);

    /**
     * Get send_time
     * @return string|null
     */
    public function getSendTime();

    /**
     * Set send_time
     * @param string $sendTime
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setSendTime($sendTime);

    /**
     * Get message
     * @return string|null
     */
    public function getMessage();

    /**
     * Set message
     * @param string $message
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setMessage($message);

    /**
     * Get is_notify_customer
     * @return string|null
     */
    public function getIsNotifyCustomer();

    /**
     * Set is_notify_customer
     * @param string $isNotifyCustomer
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setIsNotifyCustomer($isNotifyCustomer);

    /**
     * Get is_visible_frontend
     * @return string|null
     */
    public function getIsVisibleFrontend();

    /**
     * Set is_visible_frontend
     * @param string $isVisibleFrontend
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setIsVisibleFrontend($isVisibleFrontend);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface
     */
    public function setStatus($status);
}
