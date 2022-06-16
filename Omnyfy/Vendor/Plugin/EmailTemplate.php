<?php
declare(strict_types=1);

namespace Omnyfy\Vendor\Plugin;

class EmailTemplate
{

    public function beforeBeforeSave(\Magento\Email\Model\Template $subject)
    {
        $subject->setData('is_legacy', 1);
        return [];
    }
}