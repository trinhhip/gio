<?php

namespace Omnyfy\RebateCore\Model\Mail;

use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Mime\PartFactory as MimePartFactory;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\MessageFactory as MimeMessageFactory;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /** @var MimePart[] */
    private $parts = [];

    /** @var MimeMessageFactory */
    private $mimeMessageFactory;

    /** @var MimePartFactory */
    private $mimePartFactory;

    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MimePartFactory $mimePartFactory,
        MimeMessageFactory $mimeMessageFactory,
        MessageInterfaceFactory $messageFactory = null
    ) {
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory,
            $messageFactory
        );

        $this->mimePartFactory    = $mimePartFactory;
        $this->mimeMessageFactory = $mimeMessageFactory;
    }

    protected function prepareMessage()
    {
        parent::prepareMessage();

        $mimeMessage = $this->getMimeMessage($this->message);

        foreach ($this->parts as $part) {
            $mimeMessage->addPart($part);
        }

        $this->message->setBody($mimeMessage);

        return $this;
    }

    public function addAttachment(
        $body,
        $filename = null,
        $mimeType = Mime::TYPE_OCTETSTREAM,
        $disposition = Mime::DISPOSITION_ATTACHMENT,
        $encoding = Mime::ENCODING_BASE64
    ) {
        $this->parts[] = $this->createMimePart($body, $mimeType, $disposition, $encoding, $filename);
        return $this;
    }

    private function createMimePart(
        $content,
        $type = Mime::TYPE_OCTETSTREAM,
        $disposition = Mime::DISPOSITION_ATTACHMENT,
        $encoding = Mime::ENCODING_BASE64,
        $filename = null
    ) {
        /** @var MimePart $mimePart */
        $mimePart = $this->mimePartFactory->create(['content' => $content]);
        $mimePart->setType($type);
        $mimePart->setDisposition($disposition);
        $mimePart->setEncoding($encoding);

        if ($filename) {
            $mimePart->setFileName($filename);
        }

        return $mimePart;
    }

    private function getMimeMessage(MessageInterface $message)
    {
        $body = $message->getBody();

        if ($body instanceof MimeMessage) {
            return $body;
        }

        /** @var MimeMessage $mimeMessage */
        $mimeMessage = $this->mimeMessageFactory->create();

        if ($body) {
            $mimePart = $this->createMimePart((string)$body, Mime::TYPE_TEXT, Mime::DISPOSITION_INLINE);
            $mimeMessage->setParts([$mimePart]);
        }

        return $mimeMessage;
    }
}