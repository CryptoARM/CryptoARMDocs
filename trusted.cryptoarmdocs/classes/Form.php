<?php

namespace Trusted\CryptoARM\Docs;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

require_once TR_CA_DOCS_MODULE_DIR_CLASSES . '/tcpdf_min/tcpdf.php';

Loader::includeModule('iblock');


class Form {
    public static function getIBlocks() {
        $responce = \CIBlock::GetList(
            Array(
                "sort" => "asc",
                "name" => "asc",
            ),
            Array(
                "TYPE" => "tr_ca_docs_form",
                "CHECK_PERMISSIONS" => "N",
            )
        );

        $iBlocks = [];

        while ($arIblock = $responce->Fetch()) {
            $iBlocks[htmlspecialcharsEx($arIblock["ID"])] = htmlspecialcharsEx($arIblock["NAME"]);
        }

        return $iBlocks;
    }

    public static function getIBlocksId() {
        return array_keys(self::getIBlocks());
    }

    public static function getIBlockName($iBlockId) {
        return self::getIBlocks()[$iBlockId];
    }

    public static function getIBlockProperty($iBlockId) {
        $responce = \CIBlockProperty::GetList(
            Array(
                "sort" => "asc",
                "name" => "asc",
            ),
            Array(
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $iBlockId,
            )
        );

        $properties = [];

        while ($prop_fields = $responce->GetNext()) {
            $properties[$prop_fields["ID"]]["ID"] = $prop_fields["ID"];
            $properties[$prop_fields["ID"]]["NAME"] = $prop_fields["NAME"];
            $properties[$prop_fields["ID"]]["PROPERTY_TYPE"] = $prop_fields["PROPERTY_TYPE"];
            $properties[$prop_fields["ID"]]["MULTIPLE"] = $prop_fields["MULTIPLE"];
            $properties[$prop_fields["ID"]]["LIST_TYPE"] = $prop_fields["LIST_TYPE"];
            $properties[$prop_fields["ID"]]["DEFAULT_VALUE"] = $prop_fields["DEFAULT_VALUE"];
            $properties[$prop_fields["ID"]]["IS_REQUIRED"] = $prop_fields["IS_REQUIRED"];
            $properties[$prop_fields["ID"]]["SORT"] = $prop_fields["SORT"];
            $properties[$prop_fields["ID"]]["CODE"] = $prop_fields["CODE"];
            $properties[$prop_fields["ID"]]["USER_TYPE"] = $prop_fields["USER_TYPE"];
        }

        $responceAdditional = \CIBlockPropertyEnum::GetList(
            Array(
                "sort" => "asc",
                "name" => "asc",
            ),
            Array(
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $iBlockId,
            )
        );

        while ($propAdd_fields = $responceAdditional->GetNext()) {
            $properties[$propAdd_fields["PROPERTY_ID"]]["ADDITIONAL"][$propAdd_fields["ID"]] = $propAdd_fields["VALUE"];
        }

        return $properties;
    }

    public static function getIBlockElements($by, $order, $arFilter) {
        $iBlocksId = self::getIBlocksId();
        $iBlocksElements = [];

        if (!$arFilter["IBLOCK_ID"]) {
            $arFilter = array_merge(
                $arFilter,
                ["IBLOCK_ID" => $iBlocksId]
            );
        }

        $db_elemens = \CIBlockElement::GetList(
            array($by => $order),
            $arFilter
        );

        while ($obElement = $db_elemens->GetNextElement()) {
            $el = $obElement->GetFields();
            $el["PROPERTIES"] = $obElement->GetProperties();
            $iBlocksElements[$el["ID"]] = $el;
        }

        return $iBlocksElements;
    }

    public static function addIBlock($iBlockId, $props, $userId) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in Form::addIBlock",
        );

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            return $res;
        }

        $iBlockElement = new \CIBlockElement;

        $response = Array(
            "MODIFIED_BY" => $userId,
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $iBlockId,
            "PROPERTY_VALUES" => $props,
            "NAME" => "Form",
            "ACTIVE" => "Y",
        );

        if ($iBlockElementId = $iBlockElement->Add($response)) {
            $res = array(
                "success" => true,
                "message" => "iBlockElement added",
                "id" => $iBlockElementId
            );
        }

        return $res;
    }

    public static function getIBlockElementInfo($iBlockId, $iBlockElementId) {
        $form = \CIBlockElement::GetList(
            array('SORT' => 'ASC'),
            array(
                'ID' => $iBlockElementId,
                'IBLOCK_ID' => $iBlockId,
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
                if (!$doc) {
                    continue;
                }
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
        return $props;
    }

    public static function standardizationIBlockProps($props) {
        $someArray = array();
        foreach ($props as $key => $value) {
            if (stristr($key, "input_date_")) {
                $key = str_ireplace("input_date_", "", $key);
                if (Utils::checkValueForNotEmpty($value)) {
                    $someArray[$key] = date_format(date_create($value), 'd.m.Y');
                }
                continue;
            }
            if (stristr($key, "input_checkbox_")) {
                $key = str_ireplace("input_checkbox_", "", $key);
                $keyValue = preg_split("/\D/", $key);
                if (Utils::checkValueForNotEmpty($keyValue)) {
                    $someArray[$keyValue[0]][] = $keyValue[1];
                }
                continue;
            }
            if (stristr($key, "input_text_")) {
                $key = str_ireplace("input_text_", "", $key);
                if (Utils::checkValueForNotEmpty($value)) {
                    $someArray[$key] = $value;
                }
                continue;
            }
            if (stristr($key, "input_number_")) {
                $key = str_ireplace("input_number_", "", $key);
                if (Utils::checkValueForNotEmpty($value)) {
                    $someArray[$key] = $value;
                }
                continue;
            }
            if (stristr($key, "input_radio_")) {
                $key = str_ireplace("input_radio_", "", $key);
                if (Utils::checkValueForNotEmpty($value)) {
                    $someArray[$key] = $value;
                }
                continue;
            }
            if (stristr($key, "input_html_")) {
                $key = str_ireplace("input_html_", "", $key);
                if (Utils::checkValueForNotEmpty($value)) {
                    $someArray[$key] = $value;
                }
                continue;
            }
            if (stristr($key, "input_file_id_")) {
                $key = str_ireplace("input_file_id_", "", $key);
                if (Utils::checkValueForNotEmpty($value)) {
                    $someArray[$key] = $value;
                }
                continue;
            }
        }

        return $someArray;
    }

    public static function addIBlockForm($iBlockId, $props) {
        $res = array(
            'success' => false,
            'message' => 'Unknown error in Form::addIBlockForm',
        );

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            return $res;
        }

        $props = self::standardizationIBlockProps($props);

        $addResult = self::addIBlock($iBlockId, $props, Utils::currUserId());

        $res['success'] = true;
        $res['message'] = $addResult['message'];
        $res['data'] = $addResult['id'];

        return $res;
    }

    static function createPDF($iBlockId, $iBlockElementId) {

        $res = array(
            'success' => false,
            'message' => 'Unknown error in Form::createPDF',
        );

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            return $res;
        }

        $props = self::getIBlockElementInfo($iBlockId, $iBlockElementId);

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
                if (Utils::checkValueForNotEmpty($value["FILE_NAME"])) {
                    $pdfText .= '
                    <tr>
                        <td><b>' . $value["NAME"] . '</b></td>
                        <td>' . $value["FILE_NAME"] . '</td>
                    </tr>
                    <tr>
                        <td><b>' . Loc::getMessage('TR_CA_DOC_PDF_FILE_HASH') . '</b></td>
                        <td>' . $value["HASH"] . '</td>
                    </tr>';
                }
                continue;
            }

            if ($value["MULTIPLE"] == "Y") {
                if (Utils::checkValueForNotEmpty($value["VALUE"])) {
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
                }
                continue;
            }

            if ($value["VALUE"]["TYPE"] == "HTML") {
                if (Utils::checkValueForNotEmpty($value['VALUE']['TEXT'])) {
                    $pdfText .= '
                    <tr>
                        <td colspan="2">' . (htmlspecialchars_decode($value['VALUE']['TEXT'])) . '</td>
                    </tr>';
                }
                continue;
            }

            if (Utils::checkValueForNotEmpty($value["VALUE"])) {
                $pdfText .= '
                <tr>
                    <td><b>' . $value["NAME"] . '</b></td>
                    <td>' . $value["VALUE"] . '</td>
                </tr>';
            }
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

        $pdf->Output($newDocDir, 'F');
        $props = new PropertyCollection();
        $props->add(new Property("USER", (string)Utils::currUserId()));
        $props->add(new Property("FORM", (string)$iBlockElementId["data"]));
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

    static function sendEmail($docsIds, $toUser = false, $toAdditional = false) {
        $res = array(
            'success' => false,
            'message' => 'Unknown error in Form::sendEmail or nothing to send',
        );

        global $USER;

        if ($toUser) {
            $arEventFields = array(
                "EMAIL" => $toUser
            );

            $response = Email::sendEmail($docsIds, "MAIL_EVENT_ID_FORM", $arEventFields, "MAIL_TEMPLATE_ID_FORM");

            if ($response["success"]) {
                $res = array(
                    'success' => false,
                    'message' => $response["message"],
                );
            }
        }

        if ($toAdditional) {
            if (Utils::validateEmailAddress($toAdditional)) {
                $arEventFields = array(
                    "EMAIL" => $toAdditional,
                    "FORM_USER" => Utils::getUserName(Utils::getUserIdByEmail($toUser))
                );

                $response = Email::sendEmail($docsIds, "MAIL_EVENT_ID_FORM_TO_ADMIN", $arEventFields, "MAIL_TEMPLATE_ID_FORM_TO_ADMIN");

                if ($response["success"]) {
                    $res = array(
                        'success' => false,
                        'message' => $response["message"],
                    );
                }
            }
        }

        return $res;
    }

    static function removeIBlockAndDocs($ids) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Form::removeIBlockAndDocs"
        ];

        foreach ($ids as $id) {
            $docs = Database::getDocumentsByPropertyTypeAndValue("FORM", $id);
            $docList = $docs->getList();

            $docsId = [];

            foreach ($docList as $doc) {
                $docsId["ids"][] = $doc->getId();
            }

            $responce = AjaxCommand::remove($docsId);
            $responceIBlock = \CIBlockElement::Delete($id);
        }

        if ($responceIBlock) {
            $res = [
                "success" => true,
                "message" => "ok"
            ];
        }

        return $res;
    }
}
