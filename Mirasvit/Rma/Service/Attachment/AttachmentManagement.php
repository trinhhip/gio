<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.25
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rma\Service\Attachment;

use http\Message;
use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Rma\Model\AttachmentFactory;
use Magento\Framework\Url;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Dir\Reader;
use Mirasvit\Rma\Api\Repository\AttachmentRepositoryInterface;
use Mirasvit\Rma\Model\ResourceModel\Attachment\CollectionFactory;
use Mirasvit\Rma\Api\Service\Mimetypes\MimetypesServiceInterface;
use Mirasvit\Rma\Api\Config\AttachmentConfigInterface;

/**
 *  We put here only methods directly connected with Attachment properties
 */
class AttachmentManagement implements \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface
{
    /**
     * @var \Mirasvit\Rma\Model\AttachmentFactory
     */
    private $attachmentFactory;
    /**
     * @var \Magento\Framework\Url
     */
    private $urlManager;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var \Mirasvit\Rma\Api\Service\Mimetypes\MimetypesServiceInterface
     */
    private $mimetypesService;
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleReader;
    /**
     * @var \Mirasvit\Rma\Api\Repository\AttachmentRepositoryInterface
     */
    private $attachmentRepository;
    /**
     * @var \Mirasvit\Rma\Api\Config\AttachmentConfigInterface
     */
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Attachment\CollectionFactory
     */
    private $attachmentCollectionFactory;

    private $config;

    /**
     * AttachmentManagement constructor.
     */
    public function __construct(
        AttachmentFactory $attachmentFactory,
        Url $urlManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        Reader $moduleReader,
        AttachmentRepositoryInterface $attachmentRepository,
        CollectionFactory $attachmentCollectionFactory,
        MimetypesServiceInterface $mimetypesService,
        AttachmentConfigInterface $config
    ) {
        $this->attachmentFactory            = $attachmentFactory;
        $this->urlManager                   = $urlManager;
        $this->searchCriteriaBuilder        = $searchCriteriaBuilder;
        $this->request                      = $request;
        $this->moduleReader                 = $moduleReader;
        $this->mimetypesService             = $mimetypesService;
        $this->attachmentRepository         = $attachmentRepository;
        $this->attachmentCollectionFactory  = $attachmentCollectionFactory;
        $this->config                       = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachmentsByMessage(\Mirasvit\Rma\Api\Data\MessageInterface $message)
    {
        return $this->getAttachments('message', $message->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function saveAttachments(
        $itemType,
        $itemId,
        $field = 'attachment'
    ) {
        /** @var \Zend\Stdlib\Parameters $filesData */
        $filesData = $this->request->getFiles();
        $allowedFileExtensions = $this->config->getFileAllowedExtensions();
        $fileSizeLimit = (float)$this->config->getFileSizeLimit() * 1024 * 1024;

        if (!$this->hasAttachments($field)) {
            return false;
        }
        $i = 0;
        $error = false;
        $files = $filesData->toArray();
        foreach ($files[$field] as $index => $fileInfo) {
            if ($fileInfo['name'] == '' || !empty($fileInfo['is_saved']) || UPLOAD_ERR_OK !== $fileInfo['error']) {
                $error = true;
                continue;
            }

            $type = $fileInfo['type'];
            $size = $fileInfo['size'];
            $ext  = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);

            if (count($allowedFileExtensions) && !in_array(strtolower($ext), $allowedFileExtensions)) {
                $error = true;
                continue;
            }

            if ($fileSizeLimit && $size > $fileSizeLimit) {
                $error = true;
                continue;
            }

            $this->_saveFile($itemType, $itemId, $fileInfo['name'], $fileInfo['tmp_name'], $type, $size);
            ++$i;
            $fileInfo['is_saved'] = 1;
        }
        if ($error) {
            throw new LocalizedException(__('Error. Uploaded file does not match requirements.'));
        }

        return true;
    }

    /**
     * @param string $itemType
     * @param int    $itemId
     * @param string $name
     * @param string $tmpName
     * @param string $fileType
     * @param string $size
     * @param bool   $isReplace
     * @return void
     */
    protected function _saveFile($itemType, $itemId, $name, $tmpName, $fileType, $size, $isReplace = false)
    {
        /** @var \Mirasvit\Rma\Model\Attachment $attachment */
        $attachment = false;
        if ($isReplace) {
            $attachment = $this->getAttachment($itemType, $itemId);
        }

        if (!$attachment) {
            $attachment = $this->attachmentRepository->create();
        }
        set_error_handler(function() { /* ignore errors */ });
        //@tofix - need to check for max upload size and alert error
        $body = file_get_contents(addslashes($tmpName));
        restore_error_handler();

        $attachment
            ->setItemType($itemType)
            ->setItemId($itemId)
            ->setName($name)
            ->setSize($size)
            ->setBody($body)
            ->setType($fileType)
            ->save();

    }


    /**
     * @param string $itemType
     * @param string $itemId
     * @param bool|string $field
     * @return bool
     * @throws LocalizedException
     */
    public function saveAttachment($itemType, $itemId, $field = false)
    {
        if (!$this->hasAttachments($field)) {
            $fileData = $this->request->getParam($field);
            if (isset($fileData['delete']) && $fileData['delete']) {
                $attachment = $this->getAttachment($itemType, $itemId);
                $attachment->delete();

                return true;
            }

            return false;
        }
        /** @var \Zend\Stdlib\Parameters $filesData */
        $filesData = $this->request->getFiles();
        $files = $filesData->toArray();

        $result = $this->isAllowedExtension($files[$field]['name'], mime_content_type($files[$field]['tmp_name']));
        if (!$result) {
            throw new LocalizedException(__('Error. Uploaded file does not match requirements.'));
        }
        $this->_saveFile(
            $itemType,
            $itemId,
            $files[$field]['name'],
            $files[$field]['tmp_name'],
            $files[$field]['type'],
            $files[$field]['size'],
            true
        );

        return true;
    }

    /**
     * @param string $filename
     * @param string $mimetype
     * @return bool
     */
    public function isAllowedExtension($filename, $mimetype)
    {
        $allowedExtensions = $this->config->getShippingLabelsAllowedExtensions();
        if (!$allowedExtensions) {
            return true;
        }

        $result = false;
        $parts  = explode('.', $filename);
        $types  = $this->mimetypesService->getList();
        if ($parts) {
            $extension = strtolower(end($parts));
            if (in_array($extension, $allowedExtensions) && $types[$extension] == $mimetype) {
                $result = true;
            }
        } else {
            foreach ($allowedExtensions as $extension) {
                if ($types[trim($extension)] == $mimetype) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

     /**
     * {@inheritdoc}
     */
    public function getAttachments($itemType, $itemId)
    {
        $attachments = $this->attachmentCollectionFactory->create()
            ->addFieldToFilter('item_id', $itemId)
            ->addFieldToFilter('item_type', $itemType)
            ->addFieldToSelect(['attachment_id'])
            ->addFieldToSelect(['item_type'])
            ->addFieldToSelect(['item_id'])
            ->addFieldToSelect(['uid'])
            ->addFieldToSelect(['name'])
            ->addFieldToSelect(['type'])
            ->addFieldToSelect(['size'])
            ->addFieldToSelect(['created_at'])
        ;

        $attachmentsArray = [];

        foreach ($attachments as $attachment ) {
            if ($attachment) {
                array_push($attachmentsArray, $attachment);
            }
        }

        return $attachmentsArray;
    }

    /**
     * @param string $itemType
     * @param int    $itemId
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface[] $items
     */
    public function getNewMessageAttachment($itemType, $itemId) {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('item_id', $itemId)
            ->addFilter('item_type', $itemType)
            ->create();

        return $this->attachmentRepository->getList($searchCriteria)->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttachments($field = 'attachment')
    {
        /** @var \Zend\Stdlib\Parameters $filesData */
        $filesData = $this->request->getFiles();
        $files = $filesData->toArray();

        return !empty($files[$field]) && (!empty($files[$field]['name']) || !empty($files[$field][0]['name']));
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachment($itemType, $itemId)
    {
        $items = $this->getAttachments($itemType, $itemId);

        return array_shift($items);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($attachment)
    {
        return $this->urlManager->getUrl('rma/attachment/download', ['uid' => $attachment->getUid()]);
    }

    /**
     * @param \Mirasvit\Helpdesk\Model\Email $email
     * @param \Mirasvit\Rma\Api\Data\MessageInterface       $message
     * @return void
     */
    public function copyEmailAttachments($email, $message)
    {
        foreach ($email->getAttachments() as $emailAttachment) {
            $this->attachmentFactory->create()
                ->setEntityId($message->getId())
                ->setEntityType('COMMENT')
                ->setName($emailAttachment->getName())
                ->setSize($emailAttachment->getSize())
                ->setBody($emailAttachment->getBody())
                ->setType($emailAttachment->getType())
                ->save();
        }
    }
}
