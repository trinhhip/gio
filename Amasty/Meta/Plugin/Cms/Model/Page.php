<?php
namespace Amasty\Meta\Plugin\Cms\Model;

class Page
{

    /**
     * @var \Amasty\Meta\Helper\Data
     */
    protected $data;

    public function __construct(
        \Amasty\Meta\Helper\Data $dataHelper
    ) {
        $this->data = $dataHelper;
    }

    public function afterGetMetaKeywords(
        $metaKeywords
    ) {
        $replacedMetaKeywords = $this->data->getReplaceData('meta_keywords');
        if ($replacedMetaKeywords) {
            return $replacedMetaKeywords;
        }
        return $metaKeywords;
    }
}