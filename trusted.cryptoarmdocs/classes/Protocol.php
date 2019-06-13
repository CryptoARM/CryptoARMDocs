<?php
namespace Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;

require_once TR_CA_DOCS_MODULE_DIR_CLASSES . '/tcpdf_min/tcpdf.php';

class Protocol
{

    static function createProtocol($doc)
    {
        $pdf = new \TCPDF(
            'P',        // orientation - [P]ortrait or [L]andscape
            'mm',       // measure unit
            'A4',       // page format
            true,       // unicode
            'UTF-8',    // encoding for conversions
            false,      // cache, deprecated
            false       // pdf/a mode
        );

        $docName = $doc->getName();

        $author = Loc::getMessage('TR_CA_DOC_MODULE_NAME');
        $title = $author . ': ' . Loc::getMessage('TR_CA_DOC_PROTOCOL_TITLE') . $docName;

        $pdf->SetCreator($author);
        $pdf->SetAuthor($author);
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords('CryptoARM, document, digital signature');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        // if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        //     require_once(dirname(__FILE__).'/lang/eng.php');
        //     $pdf->setLanguageArray($l);
        // }

        $pdf->SetFont('times', 'BI', 20);

        $pdf->AddPage();

        $txt = $docName;

        $pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);

        $pdf->Output($doc->getName() . '_protocol.pdf', 'D');
    }

}

