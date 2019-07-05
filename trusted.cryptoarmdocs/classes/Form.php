<?php

namespace Trusted\CryptoARM\Docs;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

Loader::includeModule('iblock');

require_once TR_CA_DOCS_MODULE_DIR_CLASSES . '/tcpdf_min/tcpdf.php';

class Form {
    public static function addIBlock($iBlockTypeId, $props, $userId) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.addIBlock",
        );

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

        global $USER;
        if (!$USER->IsAuthorized()) {
            $res['message'] = 'No authorization';
            return $res;
        }

        $props = self::standardizationIBlockProps($props);

        $addResult = self::addIBlock($iBlockTypeId, $props, $USER->GetID());

        $res['success'] = true;
        $res['message'] = $addResult['message'];
        $res['data'] = $addResult['id'];

        return $res;
    }

    static function createPDF($iBlockId) {


        $form = \CIBlockElement::GetList(
            array('SORT' => 'ASC'),
            array(
                'ID' => $iBlockId,
                'IBLOCK_ID' => 4,
            )
        )->GetNextElement();

        $formFields = $form->GetFields();
        $formProps = $form->GetProperties();

        $nusnieFields = [
            "TIMESTAMP_X" => $formFields["TIMESTAMP_X"],
            "CREATED_USER_NAME" => $formFields["CREATED_USER_NAME"],
        ];

        foreach ($formProps as $key => $value) {

            $nusnieProps[$key] = [
                "NAME" => $value["NAME"],
                "VALUE" => $value["VALUE"],
                "MULTIPLE" => $value["MULTIPLE"],
            ];

            if (stristr($value["CODE"], "DOC_FILE")) {
                $doc = Database::getDocumentById((int)$value["VALUE"]);

                $nusnieProps[$key] = array_merge(
                    $nusnieProps[$key],
                    [
                        "FILE" => true,
                        "FILE_NAME" => $doc->getName(),
                        "HASH" => $doc->getHash(),

                    ]
                );
            }
        }

        Utils::dump("formFields", $nusnieFields);
        Utils::dump("formProps", $nusnieProps);

        return $formProps;

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

        // set default font subsetting mode
        // $pdf->setFontSubsetting(true);

        // set some language-dependent strings (optional)
        // if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        //     require_once(dirname(__FILE__).'/lang/eng.php');
        //     $pdf->setLanguageArray($l);
        // }

        $pdf->SetFont('dejavuserif', '', 11);

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
                    '{DOC_SIGNATURES}' => Loc::getMessage('TR_CA_DOC_SIGNATURES'),
                    '{DOC_SIGNATURES_VALUE}' => $doc->getSignaturesToTable(array('time', 'name', 'org', 'algorithm')),
                )
            );

            $pdf->writeHTMLCell(0, 0, '', '', $signaturesText, 0, 1, 0, true, '', true);
        }

        $pdf->Output($doc->getName() . '_protocol.pdf', 'D');
    }
}