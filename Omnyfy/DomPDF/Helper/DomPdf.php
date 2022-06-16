<?php

namespace Omnyfy\DomPDF\Helper;

use Dompdf\Dompdf as PDF;
class DomPdf extends \Magento\Framework\App\Helper\AbstractHelper implements \Omnyfy\Core\Helper\DomPdfInterface
{
    public $pdf;

    /**
     * Dompdf constructor.
     */
    public function __construct(PDF $domPdf)
    {
        $this->pdf = $domPdf;
    }

    public function newDompdf()
    {
        $this->pdf = new PDF();
    }

    /**
     * Load html
     *
     * @param $html
     */
    public function setData($html)
    {
        $this->pdf->loadHtml($html);

        //load image
        $options = $this->pdf->getOptions();
        $options->set(['isRemoteEnabled' => true]);
        $this->pdf->setOptions($options);

        $this->pdf->setPaper('A4','landsacpe');
        $this->pdf->render();
    }

    /**
     * @return $this
     */
    public function render()
    {
        return $this->pdf->stream('vendor_invoice_' . time());
    }

    public function renderPickList()
    {
        return $this->pdf->stream('vendor_order_' . time());
    }

    public function save()
    {
        return $this->pdf->output();
    }
    
    public function uploadPdf()
    {
        return $this->pdf->output();
    }
}
