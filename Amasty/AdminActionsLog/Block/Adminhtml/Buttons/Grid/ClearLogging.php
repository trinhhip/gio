<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Block\Adminhtml\Buttons\Grid;

use Amasty\AdminActionsLog\Block\Adminhtml\Buttons\GenericButton;

class ClearLogging extends GenericButton
{
    const ADMIN_RESOURCE = 'Amasty_AdminActionsLog::clear_logging';

    public function getButtonData(): array
    {
        if ($this->authorization->isAllowed(self::ADMIN_RESOURCE)) {
            $alertMessage = __('Are you sure you want to do this?');
            $onClick = sprintf(
                'deleteConfirm("%s", "%s")',
                $alertMessage,
                $this->getClearLogUrl()
            );

            return [
                'label' => __('Clear Log'),
                'class' => 'primary',
                'on_click' => $onClick,
                'sort_order' => 10,
            ];
        }

        return [];
    }

    public function getClearLogUrl(): string
    {
        return $this->getUrl('*/*/clear');
    }
}
