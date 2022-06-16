<?php


namespace OmnyfyCustomzation\Catalog\Plugin\SearchAutocomplete\Model;


class Result
{
    public function afterToArray(\Mirasvit\SearchAutocomplete\Model\Result $subject, $result)
    {
        if (isset($result['textEmpty'])) {
            $result['textEmpty'] = $this->htmlEntityDecode(__('Discoveries await. Please try again!'));
        }
        return $result;
    }

    private function htmlEntityDecode($text)
    {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}