<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Ui\Component\Control\Template;

class DeleteButton extends GenericButton
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];

        if ($templateId = $this->getTemplateId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => sprintf(
                    'deleteConfirm("%s", "%s")',
                    __('Are you sure you want to delete this rule?'),
                    $this->getUrl('*/*/delete', ['id' => $templateId])
                ),
                'sort_order' => 20,
            ];
        }

        return $data;
    }
}
