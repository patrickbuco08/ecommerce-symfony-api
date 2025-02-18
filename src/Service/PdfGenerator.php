<?php

namespace Bocum\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Bocum\Entity\Order;
use Twig\Environment;

class PdfGenerator
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function generateInvoice(Order $order): string
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $html = $this->twig->render('invoice/invoice.html.twig', ['order' => $order]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function generateAndSaveInvoice(Order $order, string $invoiceDir): string
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $html = $this->twig->render('invoice/invoice.html.twig', ['order' => $order]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filePath = $invoiceDir . '/invoice-' . $order->getId() . '.pdf';
        file_put_contents($filePath, $dompdf->output());

        return $filePath;
    }
}
