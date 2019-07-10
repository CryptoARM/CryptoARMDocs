<?php

namespace Trusted\CryptoARM\Docs;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

require_once TR_CA_DOCS_MODULE_DIR_CLASSES . '/tcpdf_min/tcpdf.php';

Loader::includeModule('iblock');


class Form {
    public static function addIBlock($iBlockTypeId, $props, $userId) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.addIBlock",
        );

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            return $res;
        }

        $iBlock = new \CIBlockElement;

        $response = Array(
            "MODIFIED_BY" => $userId, // элемент изменен текущим пользователем
            "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
            "IBLOCK_ID" => $iBlockTypeId,
            "PROPERTY_VALUES" => $props,
            "NAME" => "Form",
            "ACTIVE" => "Y",
        );

        if ($iBlockId = $iBlock->Add($response)) {
            $res = array(
                "success" => true,
                "message" => "iBlock added",
                "id" => $iBlockId
            );
        }

        return $res;
    }

    public static function standardizationIBlockProps($props) {
        $someArray = array();
        foreach ($props as $key => $value) {
            if (stristr($key, "input_date_")) {
                $key = str_ireplace("input_date_", "", $key);
                $someArray[$key] = date_format(date_create($value), 'd.m.Y');
                continue;
            }
            if (stristr($key, "input_checkbox_")) {
                $key = str_ireplace("input_checkbox_", "", $key);
                $keyValue = preg_split("/\D/", $key);
                $someArray[$keyValue[0]][] = $keyValue[1];
                continue;
            }
            if (stristr($key, "input_text_")) {
                $key = str_ireplace("input_text_", "", $key);
                $someArray[$key] = $value;
                continue;
            }
            if (stristr($key, "input_number_")) {
                $key = str_ireplace("input_number_", "", $key);
                $someArray[$key] = $value;
                continue;
            }
            if (stristr($key, "input_radio_")) {
                $key = str_ireplace("input_radio_", "", $key);
                $someArray[$key] = $value;
                continue;
            }
            if (stristr($key, "input_html_")) {
                $key = str_ireplace("input_html_", "", $key);
                $someArray[$key] = $value;
                continue;
            }
            if (stristr($key, "input_file_id_")) {
                $key = str_ireplace("input_file_id_", "", $key);
                if ($value || $value === 0 || $value === 0.0 || $value === '0') {
                    $someArray[$key] = $value;
                }
                continue;
            }
        }

        return $someArray;
    }

    public static function addIBlockForm($iBlockTypeId, $props) {
        $res = array(
            'success' => false,
            'message' => 'Unknown error in addIBlockForm',
        );

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            return $res;
        }

        $props = self::standardizationIBlockProps($props);

        $addResult = self::addIBlock($iBlockTypeId, $props, Utils::currUserId());

        $res['success'] = true;
        $res['message'] = $addResult['message'];
        $res['data'] = $addResult['id'];

        return $res;
    }

    static function createPDF($iblockTypeid, $iBlockId) {

        $res = array(
            'success' => false,
            'message' => 'Unknown error in createPDF',
        );

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            return $res;
        }

        $form = \CIBlockElement::GetList(
            array('SORT' => 'ASC'),
            array(
                'ID' => $iBlockId,
                'IBLOCK_ID' => $iblockTypeid,
            )
        )->GetNextElement();

        $props = [];

        $formProps = $form->GetProperties();

        foreach ($formProps as $key => $value) {
            $props[$key] = [
                "NAME" => $value["NAME"],
                "VALUE" => $value["VALUE"],
                "MULTIPLE" => $value["MULTIPLE"],
            ];
            if (stristr($value["CODE"], "DOC_FILE")) {
                $doc = Database::getDocumentById((int)$value["VALUE"]);

                $props[$key] = array_merge(
                    $props[$key],
                    [
                        "FILE" => true,
                        "FILE_NAME" => $doc->getName(),
                        "HASH" => $doc->getHash(),

                    ]
                );
            }
        }

        $pdf = new \TCPDF(
            'P',        // orientation - [P]ortrait or [L]andscape
            'mm',       // measure unit
            'A4',       // page format
            true,       // unicode
            'UTF-8',    // encoding for conversions
            false,      // cache, deprecated
            false       // pdf/a mode
        );

        $pdfOwner = Utils::getUserName(Utils::currUserId());
        $dateCreation = date("Y-m-d H:i:s");

        $author = Loc::getMessage('TR_CA_DOC_MODULE_NAME');
        $title = Loc::getMessage('TR_CA_DOC_PDF_FORM_TITLE') . " " . $pdfOwner . " " . $dateCreation;
        $title = Utils::mb_basename($title);
        $headerText = Loc::getMessage('TR_CA_DOC_MODULE_DESC') . "\n" . Loc::getMessage('TR_CA_DOC_PARTNER_URI');

        $pdf->setCreator($author);
        $pdf->setAuthor($author);
        $pdf->setTitle($title);
        $pdf->setSubject($title);
        $pdf->setKeywords('CryptoARM, document, digital signature');
        $pdf->setHeaderFont(array('dejavuserif', 'B', 11));
        $pdf->setHeaderData('logo_docs.png', 14, $author, $headerText);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('dejavuserif', '', 11);
        $pdf->AddPage();

        $pdfText = '<div height="100px"></div>
        <h1 style="text-align:center;">' . Loc::getMessage('TR_CA_DOC_MODULE_NAME') . '</h1>
        <table width="600px">
            <tr>
                <td><b>' . Loc::getMessage('TR_CA_DOC_PDF_OWNER') . '</b></td>
                <td>' . $pdfOwner . '</td>
            </tr>
            <tr>
                <td><b>' . Loc::getMessage('TR_CA_DOC_PDF_CREATE_TIME') . '</b></td>
                <td>' . $dateCreation . '</td>
            </tr>';

        foreach ($props as $key => $value) {
            if ($value["HASH"]) {
                $pdfText .= '
                <tr>
                    <td><b>' . $value["NAME"] . '</b></td>
                    <td>' . $value["FILE_NAME"] . '</td>
                </tr>
                <tr>
                    <td><b>' . Loc::getMessage('TR_CA_DOC_PDF_FILE_HASH') . '</b></td>
                    <td>' . $value["HASH"] .  '</td>
                </tr>';
                continue;
            }

            if ($value["MULTIPLE"] == "Y") {
                $propertyString = "";
                foreach ($value["VALUE"] as $property) {
                    $propertyString .= $property . '<br>';
                }
                $propertyString = substr($propertyString, 0, -4);
                $pdfText .= '
                <tr>
                    <td><b>' . $value["NAME"] . '</b></td>
                    <td>' . $propertyString . '</td>
                </tr>';
                continue;
            }

            if ($value["VALUE"]["TYPE"] == "HTML") {
                $pdfText .= '
                <tr>
                    <td colspan="2">' . (htmlspecialchars_decode($value['VALUE']['TEXT'])) . '</td>
                </tr>';
                continue;
            }

            $pdfText .= '<tr>
                <td><b>' . $value["NAME"] . '</b></td>
                <td>' . $value["VALUE"] . '</td>
            </tr>';
        }

        $pdfText .= '</table>';

        $pdf->writeHTMLCell(
            0,      // width
            0,      // height
            '',     // x
            '',     // y
            $pdfText,
            0,      // border
            1,      // next line
            0,      // fill
            true,   // reset height
            '',     // align
            true    // autopadding
        );

        $title .= '.pdf';

        $DOCUMENTS_DIR = Option::get(TR_CA_DOCS_MODULE_ID, 'DOCUMENTS_DIR', '/docs/');

        $uniqid = (string)uniqid();
        $newDocDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/';
        mkdir($newDocDir);

        $newDocDir .= $title;
        $relativePath = '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/' . $title;

        ob_end_clean();

        $pdf->Output($newDocDir, 'FD');
        $props = new PropertyCollection();
        $props->add(new Property("USER", (string)Utils::currUserId()));
        $props->add(new Property("FORM", (string)$iBlockId));
        $doc = Utils::createDocument($relativePath, $props);
        $docId = $doc->GetId();

        if ($doc) {
            $res = array(
                'success' => true,
                'message' => 'PDF created',
                'data' => $docId
            );
        }

        return $res;
    }
}