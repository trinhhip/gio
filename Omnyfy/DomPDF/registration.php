<?php

use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Omnyfy_DomPDF',
    __DIR__
);

require_once __DIR__ . '/dompdf/autoload.inc.php';
