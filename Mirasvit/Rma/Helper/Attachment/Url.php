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



namespace Mirasvit\Rma\Helper\Attachment;

class Url extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param \Mirasvit\Rma\Api\Data\AttachmentInterface $attachment
     * @return string
     */
    public function getUrl($attachment)
    {
        return $this->_urlBuilder->getUrl('rma/attachment/download', ['uid' => $attachment->getUid()]);
    }
}