<?php

namespace Omnyfy\Core\Helper;

interface DomPdfInterface
{

    /**
     * Load html
     *
     * @param $html
     */
    public function setData($html);

    /**
     * Render PDF
     * @return $this
     */
    public function render();
}