<?php

namespace Trusted\CryptoARM\Docs;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

require_once TR_CA_DOCS_MODULE_DIR_CLASSES . '/tcpdf_min/tcpdf.php';

Loader::includeModule('iblock');

class Form {

    public static function getIBlocks() {
        $response = \CIBlock::GetList(
            [
                "sort" => "asc",
                "name" => "asc",
            ],
            [
                "TYPE" => "tr_ca_docs_form",
                "CHECK_PERMISSIONS" => "N",
            ]
        );

        $iBlocks = [];

        while ($arIblock = $response->Fetch()) {
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
        $response = \CIBlockProperty::GetList(
            [
                "sort" => "asc",
                "name" => "asc",
            ],
            [
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $iBlockId,
            ]
        );

        $properties = [];

        while ($prop_fields = $response->GetNext()) {
//            $properties[$prop_fields["ID"]] = $prop_fields;
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
            $properties[$prop_fields["ID"]]["HINT"] = $prop_fields["HINT"];
        }

        $responseAdditional = \CIBlockPropertyEnum::GetList(
            [
                "sort" => "asc",
                "name" => "asc",
            ],
            [
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $iBlockId,
            ]
        );

        while ($propAdd_fields = $responseAdditional->GetNext()) {
            $properties[$propAdd_fields["PROPERTY_ID"]]["ADDITIONAL"][$propAdd_fields["ID"]] = $propAdd_fields["VALUE"];
        }

        return $properties;
    }

    public static function getMainDocumentByFormId($id) {
        $form = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            ['ID' => $id]
        )->GetNextElement()->GetFields();

        return (int)$form["NAME"];
    }

    public static function getIBlockElements($by, $order, $arFilter) {
        $iBlocksId = self::getIBlocksId();
        $iBlocksElements = [];

        if (empty($iBlocksId)) {
            return false;
        }

        $arFilter = array_merge(
            $arFilter,
            ["IBLOCK_TYPE" => TR_CA_IB_TYPE_ID]
        );

        $dbElements = \CIBlockElement::GetList(
            [$by => $order],
            $arFilter
        );

        while ($obElement = $dbElements->GetNextElement()) {
            $el = $obElement->GetFields();
            $el["PROPERTIES"] = $obElement->GetProperties();
            $iBlocksElements[$el["ID"]] = $el;
        }

        return $iBlocksElements;
    }

    public static function getIBlockElementInfo($iBlockId, $iBlockElementId) {
        $form = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            [
                'ID' => $iBlockElementId,
                'IBLOCK_ID' => $iBlockId,
            ]
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
                if ($value["MULTIPLE"] == "Y") {
                    foreach ($value["VALUE"] as $docId) {
                        $doc = Database::getDocumentById((int)$docId);
                        if (!$doc) {
                            continue;
                        }
                        $props[$key]["FILE"] = true;
                        $props[$key]["FILE_NAME"][] = $doc->getName();
                        $props[$key]["HASH"][] = $doc->getHash();
                    }
                } else {
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
        }
        return $props;
    }

    public static function standardizationIBlockProps($props) {
        $someArray = [];
        foreach ($props as $key => $value) {
            if (stristr($key, "input_date_")) {
                $key = str_ireplace("input_date_", "", $key);
                if (Utils::isNotEmpty($value)) {
                    $someArray[$key] = date_format(date_create($value), 'd.m.Y');
                }
                continue;
            }
            if (stristr($key, "input_checkbox_")) {
                $key = str_ireplace("input_checkbox_", "", $key);
                $keyValue = explode("_", $key);
                if (Utils::isNotEmpty($keyValue)) {
                    $someArray[$keyValue[0]][] = $keyValue[1];
                }
                continue;
            }
            if (stristr($key, "input_text_")) {
                $key = str_ireplace("input_text_", "", $key);
                if (Utils::isNotEmpty($value)) {
                    $someArray[$key] = $value;
                }
                continue;
            }
            if (stristr($key, "input_number_")) {
                $key = str_ireplace("input_number_", "", $key);
                if (Utils::isNotEmpty($value)) {
                    $someArray[$key] = $value;
                }
                continue;
            }
            if (stristr($key, "input_radio_")) {
                $key = str_ireplace("input_radio_", "", $key);
                if (Utils::isNotEmpty($value)) {
                    $someArray[$key] = $value;
                }
                continue;
            }
            if (stristr($key, "input_select_")) {
                $key = str_ireplace("input_select_", "", $key);
                if (Utils::isNotEmpty($value)) {
                    $someArray[$key] = $value;
                }
                continue;
            }
            if (stristr($key, "input_html_")) {
                $key = str_ireplace("input_html_", "", $key);
                if (Utils::isNotEmpty($value)) {
                    $someArray[$key] = $value;
                }
                continue;
            }
            if (stristr($key, "input_file_")) {
                $key = str_ireplace("input_file_", "", $key);
                $keyValue = explode("_", $key);
                if (Utils::isNotEmpty($keyValue)) {
                    if ($keyValue[2] == "Y") {
                        $someArray[$keyValue[0]][] = $value;
                    } else {
                        $someArray[$keyValue[0]] = $value;
                    }
                }
                continue;
            }
        }

        return $someArray;
    }

    public static function addIBlockForm($iBlockId, $props) {
        $res = [
            'success' => false,
            'message' => 'Unknown error in Form::addIBlockForm',
        ];

        $props = self::standardizationIBlockProps($props);

        $iBlockElement = new \CIBlockElement;

        $prop = [
            "MODIFIED_BY" => Utils::currUserId(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $iBlockId,
            "PROPERTY_VALUES" => $props,
            "NAME" => "Form",
            "ACTIVE" => "Y",
        ];

        if ($iBlockElementId = $iBlockElement->Add($prop)) {
            $res = [
                "success" => true,
                "message" => "iBlockElement added",
                "data" => $iBlockElementId,
            ];
        }

        return $res;
    }

    static function createPDF($iBlockId, $iBlockElementId) {

        $res = [
            'success' => false,
            'message' => 'Unknown error in Form::createPDF',
        ];

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
        $pdf->setHeaderFont(['dejavuserif', 'B', 11]);
        $pdf->setHeaderData('logo_docs.png', 14, $author, $headerText);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
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
            if ($value["FILE"] == true) {
                if ($value["MULTIPLE"] == "Y") {
                    foreach ($value["FILE_NAME"] as $key2 => $value2) {
                        if (Utils::isNotEmpty($value["FILE_NAME"])) {
                            $pdfText .= '
                    <tr>
                        <td><b>' . $value["NAME"] . '</b></td>
                        <td>' . $value["FILE_NAME"][$key2] . '</td>
                    </tr>
                    <tr>
                        <td><b>' . Loc::getMessage('TR_CA_DOC_PDF_FILE_HASH') . '</b></td>
                        <td>' . $value["HASH"][$key2] . '</td>
                    </tr>';
                        }
                    }
                } else {
                    if (Utils::isNotEmpty($value["FILE_NAME"])) {
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
                }
                continue;
            }

            if ($value["MULTIPLE"] == "Y") {
                if (Utils::isNotEmpty($value["VALUE"])) {
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
                if (Utils::isNotEmpty($value['VALUE']['TEXT'])) {
                    $pdfText .= '
                    <tr>
                        <td colspan="2">' . (htmlspecialchars_decode($value['VALUE']['TEXT'])) . '</td>
                    </tr>';
                }
                continue;
            }

            if (Utils::isNotEmpty($value["VALUE"])) {
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

        $elementId = (string)$iBlockElementId["data"];

        $pdf->Output($newDocDir, 'F');
        $props = new PropertyCollection();
        $props->add(new Property("USER", (string)Utils::currUserId()));
        $props->add(new Property("FORM", $elementId));
        $doc = Utils::createDocument($relativePath, $props);
        $docId = $doc->GetId();

        if ($doc) {
            $res = [
                'success' => true,
                'message' => 'PDF created',
                'data' => $docId,
            ];
            $iBlockElem = new \CIBlockElement;
            $iBlockElem->Update($elementId, ["NAME" => $docId]);
        }

        return $res;
    }

    static function upload(&$doc, $extra) {
        $docs = Database::getDocumentsByPropertyTypeAndValue("FORM", $extra["formId"]);
        foreach ($docs->getList() as $doc) {
            if ($doc->getType() !== DOC_TYPE_SIGNED_FILE) {
                return false;
            }
        }
        Form::sendEmail($docs->toIdArray(), $extra["send_email_to_user"], $extra["send_email_to_admin"]);
    }

    static function sendEmail($docsIds, $toUser = false, $toAdditional = false) {
        $res = [
            'success' => false,
            'message' => 'Unknown error in Form::sendEmail or nothing to send',
        ];

        if ($toUser) {
            $arEventFields = [
                "EMAIL" => $toUser,
            ];

            $response = Email::sendEmail($docsIds, "MAIL_EVENT_ID_FORM", $arEventFields, "MAIL_TEMPLATE_ID_FORM");

            if (!$response["success"]) {
                return $response;
            }
        }

        if ($toAdditional) {
            if (!Utils::validateEmailAddress($toAdditional)) {
                $res['message'] = 'Invalid email address: ' . $toAdditional;
                return $res;
            }
            $doc = Database::getDocumentById($docsIds[0]);
            $userId = $doc->getSignersToArray()[0];
            $arEventFields = [
                "EMAIL" => $toAdditional,
                "FORM_USER" => Utils::getUserName($userId),
            ];

            $response = Email::sendEmail($docsIds, "MAIL_EVENT_ID_FORM_TO_ADMIN", $arEventFields, "MAIL_TEMPLATE_ID_FORM_TO_ADMIN");

            if (!$response["success"]) {
                return $response;
            }
        }

        $res['success'] = true;
        $res['message'] = "Emails with form were sent";
        return $res;
    }

    static function removeIBlockAndDocs($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Form::removeIBlockAndDocs",
        ];

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            return $res;
        }

        global $USER;
        $userId = $USER->GetID();

        if ($params["ids"]) {
            $ids = $params["ids"];
        } else {
            $res['message'] = 'No ids were given';
            return $res;
        }

        foreach ($ids as $id) {
            $createdBy = \CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                ['ID' => $id]
            )->GetNextElement()->GetFields()["CREATED_BY"];

            if ($createdBy != $userId) {
                $res['message'] = 'No access';
                return $res;
            }

            $docs = Database::getDocumentsByPropertyTypeAndValue("FORM", $id);
            $docList = $docs->getList();

            $docsId = [];

            foreach ($docList as $doc) {
                $doc->remove();
            }

            $responseIBlock = \CIBlockElement::Delete($id);
        }

        if ($responseIBlock) {
            $res = [
                "success" => true,
                "message" => "ok",
            ];
        }

        return $res;
    }

    static function getFirstDocument($id) {
        $doc = Database::getDocumentById($id);
        if (!$doc) {
            return '';
        }
        $lastDocId = $doc->getFirstParent()->getId();
        return "/bitrix/components/trusted/docs/ajax.php?command=content&id=$lastDocId&force=true&view=true";
    }

}

