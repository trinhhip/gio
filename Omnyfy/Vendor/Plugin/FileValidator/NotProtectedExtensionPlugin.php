<?php
namespace Omnyfy\Vendor\Plugin\FileValidator;

class NotProtectedExtensionPlugin
{
    public function aroundIsValid($subject, callable $process, $value) {
        if ($value == 'svg') {
            return true;
        } else {
            return $process($value);
        }
    }
}