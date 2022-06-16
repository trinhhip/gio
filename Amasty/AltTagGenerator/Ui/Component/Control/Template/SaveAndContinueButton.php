<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Ui\Component\Control\Template;

class SaveAndContinueButton extends GenericButton
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'on_click'  => '',
            'sort_order' => 40,
        ];
    }
}
