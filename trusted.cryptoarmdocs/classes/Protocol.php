<?php
namespace Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;

require_once TR_CA_DOCS_MODULE_DIR_CLASSES . '/tcpdf_min/tcpdf.php';

class Protocol
{

    const MAIN_TEXT = <<<HTML
<div height="80px"></div>
<h2 style="text-align:center;">{MODULE_NAME}</h1>
<div height="200px"></div>
<table width="600px">
    <tr>
        <td width="220px"><b>{DOC_NAME}:</b></td>
        <td>{DOC_NAME_VALUE}</td>
    </tr>
    {DOC_OWNER_ROW}
    <tr>
        <td><b>{DOC_FIRST_UPLOAD_TIME}:</b></td>
        <td>{DOC_FIRST_UPLOAD_TIME_VALUE}</td>
    </tr>
    <tr>
        <td><b>{DOC_HASH}:</b></td>
        <td>{DOC_HASH_VALUE}</td>
    </tr>
    <tr>
        <td><b>{DOC_ID}:</b></td>
        <td>{DOC_ID_VALUE}</td>
    </tr>
</table>
HTML;


    const DOC_OWNER_ROW = <<<HTML
<tr>
    <td><b>{DOC_OWNER}:</b></td>
    <td>{DOC_OWNER_VALUE}</td>
</tr>
HTML;


    const SIGNATURES = <<<HTML
<div height="200px"></div>
<div style="font-size: 14px"><b>{DOC_SIGNATURES}</b></div><br>
{DOC_SIGNATURES_VALUE}
HTML;


    static function replace($str, $dict)
    {
        foreach ($dict as $key => $value) {
            $str = str_replace($key, $value, $str);
        }
        return $str;
    }


    static function createProtocol($doc)
    {

        $firstDoc = $doc->getFirstParent();

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
        $title = Loc::getMessage('TR_CA_DOC_PROTOCOL_TITLE') . $docName;
        $headerText = Loc::getMessage('TR_CA_DOC_MODULE_DESC') . "\n" . 'https://Trusted.ru';

        $pdf->setCreator($author);
        $pdf->setAuthor($author);
        $pdf->setTitle($title);
        $pdf->setSubject($title);
        $pdf->setKeywords('CryptoARM, document, digital signature');

        $pdf->setHeaderFont(array('dejavusans', '', 8));
        $pdf->setHeaderData('logo_docs.png', 11, $author, $headerText, array(0, 0, 0), array(255, 255, 255));

        $pdf->setCellHeightRatio(1.5);

        $pdf->setFooterFont(array('dejavusans', '', 8));

        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set default font subsetting mode
        // $pdf->setFontSubsetting(true);

        // set some language-dependent strings (optional)
        // if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        //     require_once(dirname(__FILE__).'/lang/eng.php');
        //     $pdf->setLanguageArray($l);
        // }

        $pdf->SetFont('dejavusans', '', 8);

        $pdf->AddPage();

        // set text shadow effect
        // $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

        $mainText = self::replace(
            self::MAIN_TEXT,
            array(
                '{MODULE_NAME}' => Loc::getMessage('TR_CA_DOC_MODULE_NAME'),
                '{DOC_NAME}' => Loc::getMessage('TR_CA_DOC_NAME'),
                '{DOC_NAME_VALUE}' => $docName,
                '{DOC_FIRST_UPLOAD_TIME}' => Loc::getMessage('TR_CA_DOC_FIRST_UPLOAD_TIME'),
                '{DOC_FIRST_UPLOAD_TIME_VALUE}' => $firstDoc->getCreated(),
                '{DOC_HASH}' => Loc::getMessage('TR_CA_DOC_HASH'),
                '{DOC_HASH_VALUE}' => $doc->getHash(),
                '{DOC_ID}' => Loc::getMessage('TR_CA_DOC_ID'),
                '{DOC_ID_VALUE}' => $doc->getId(),
            )
        );

        $docOwner = $doc->getOwner();
        if ($docOwner) {
            $docOwnerText = self::replace(
                self::DOC_OWNER_ROW,
                array(
                    '{DOC_OWNER}' => Loc::getMessage('TR_CA_DOC_OWNER'),
                    '{DOC_OWNER_VALUE}' => Utils::getUserName($docOwner),
                )
            );
            $mainText = str_replace('{DOC_OWNER_ROW}', $docOwnerText, $mainText);
        } else {
            $mainText = str_replace('{DOC_OWNER_ROW}', '', $mainText);
        }

        $pdf->writeHTMLCell(
            0,      // width
            0,      // height
            '',     // x
            '',     // y
            $mainText,
            0,      // border
            1,      // next line
            0,      // fill
            true,   // reset height
            '',     // align
            true    // autopadding
        );

        if ($doc->getType() == DOC_TYPE_SIGNED_FILE) {
            $signaturesText = self::replace(
                self::SIGNATURES,
                array(
                    '{DOC_SIGNATURES}' => Loc::getMessage('TR_CA_DOC_SIGNERS'),
                    '{DOC_SIGNATURES_VALUE}' => $doc->getSignaturesToTable(array('name', 'issuer', 'serial', 'time')),
                )
            );

            $pdf->writeHTMLCell(0, 0, '', '', $signaturesText, 0, 1, 0, true, '', true);
        }

        $pdf->Output($doc->getName() . '_protocol.pdf', 'D');
    }

}

