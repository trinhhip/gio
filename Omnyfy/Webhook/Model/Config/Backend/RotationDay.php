<?php
namespace Omnyfy\Webhook\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;

class RotationDay extends Value
{
    /**
     * @return Value|void
     * @throws ValidatorException
     */
    public function beforeSave()
    {
        if ($this->getValue() > 60) {
            $this->setValue(60);
        }
        parent::beforeSave();
    }
}
