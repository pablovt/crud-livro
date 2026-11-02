<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class GeradorPdf
{
    public function gerar(string $html): string
    {
        $opcoes = (new Options())
            ->setIsRemoteEnabled(false)
            ->setDefaultFont('DejaVu Sans');

        $dompdf = new Dompdf($opcoes);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }
}
