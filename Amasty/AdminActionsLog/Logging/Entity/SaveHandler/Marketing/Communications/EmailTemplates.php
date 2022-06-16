<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Marketing\Communications;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class EmailTemplates extends Common
{
    const CATEGORY = 'admin/email_template/edit';

    protected $dataKeysIgnoreList = [
        'template_id',
        'added_at'
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Email\Model\BackendTemplate $emailTemplate */
        $emailTemplate = $metadata->getObject();

        return [
            LogEntry::ITEM => $emailTemplate->getTemplateCode(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Email Template'),
            LogEntry::ELEMENT_ID => (int)$emailTemplate->getId(),
            LogEntry::STORE_ID => (int)$emailTemplate->getStoreId()
        ];
    }
}
