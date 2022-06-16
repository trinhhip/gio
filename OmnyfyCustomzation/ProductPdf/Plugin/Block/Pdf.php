<?php


namespace OmnyfyCustomzation\ProductPdf\Plugin\Block;


class Pdf
{
    public function aroundGetHeaderHtml(\PluginCompany\ProductPdf\Block\Pdf $subject, \Closure $proceed)
    {
        return $subject->getLayout()
            ->createBlock('OmnyfyCustomzation\ProductPdf\Block\Pdf\Header')
            ->setProduct($subject->getProduct())
            ->toHtml();
    }

    public function aroundGetContentHtml(\PluginCompany\ProductPdf\Block\Pdf $subject, \Closure $proceed)
    {
        return $subject->getLayout()
            ->createBlock('OmnyfyCustomzation\ProductPdf\Block\Pdf\Content')
            ->setProduct($subject->getProduct())
            ->toHtml();
    }
    public function aroundGetFooterHtml(\PluginCompany\ProductPdf\Block\Pdf $subject, \Closure $proceed)
    {
        return $subject->getLayout()
            ->createBlock('OmnyfyCustomzation\ProductPdf\Block\Pdf\Footer')
            ->setProduct($subject->getProduct())
            ->toHtml();
    }
}
