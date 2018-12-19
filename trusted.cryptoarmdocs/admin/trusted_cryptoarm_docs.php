<?php
use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php";

$module_id = "trusted.cryptoarmdocs";

if (CModule::IncludeModuleEx($module_id) == MODULE_DEMO_EXPIRED) {
    echo GetMessage("TR_CA_DOCS_MODULE_DEMO_EXPIRED");
    die();
};

Loader::includeModule($module_id);
Loc::loadMessages(__FILE__);

// current user rights for the module
$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);

$sTableID = "Docs_ID";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);


function CheckFilter()
{
    global $FilterArr, $lAdmin;
    foreach ($FilterArr as $f)
        global $$f;
    // return false on errors
    return count($lAdmin->arFilterErrors) == 0;
}

$FilterArr = Array(
    "find",
    "find_docId",
    "find_fileName",
    "find_signInfo",
    "find_type",
    "find_status"
);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter()) {
    $arFilter = Array(
        "ID" => ($find != "" && $find_type == "id" ? $find : $find_id),
        "DOC" => $find_docId,
        "FILE_NAME" => $find_fileName,
        "SIGN" => $find_signInfo,
        "TYPE" => $find_type,
        "STATUS" => $find_status
    );
}

if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
    // selected = checkbox "for all"
    if ($_REQUEST['action_target'] == 'selected') {
        // apply filter
        $docs = Docs\Database::getDocumentIdsByFilter(array($by => $order), $arFilter);
        while($arRes = $docs->Fetch()) {
            $ids[] = $arRes['ID'];
        }
    } else {
        foreach ($arID as $ID) {
            $ID = (int)$ID;
            $ids[] = $ID;
        }
    }

    switch ($_REQUEST['action']) {
        case "sign":
            echo '<script>';
            echo 'window.parent.sign(' . json_encode($ids) . ')';
            echo '</script>';
            break;
        case "unblock":
            echo '<script>';
            echo 'window.parent.unblock(' . json_encode($ids) . ')';
            echo '</script>';
            break;
        case "remove":
            echo '<script>';
            echo 'window.parent.remove(' . json_encode($ids) . ')';
            echo '</script>';
            break;
    }
}

$docs = Docs\Database::getDocumentIdsByFilter(array($by => $order), $arFilter);
$rsData = new CAdminResult($docs, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage("TR_CA_DOCS_TITLE")));

$lAdmin->AddHeaders(array(
    array(
        "id" => "DOC",
        "content" => Loc::getMessage("TR_CA_DOCS_COL_ID"),
        "sort" => "DOC",
        "default" => true
    ),
    array(
        "id" => "FILE_NAME",
        "content" => Loc::getMessage("TR_CA_DOCS_COL_FILENAME"),
        "sort" => "FILE_NAME",
        "default" => true
    ),
    array(
        "id" => "PARENT_CREATED",
        "content" => Loc::getMessage("TR_CA_DOCS_COL_PARENT_CREATED"),
        "default" => false
    ),
    array(
        "id" => "CREATED",
        "content" => Loc::getMessage("TR_CA_DOCS_COL_CREATED"),
        "default" => false
    ),
    array(
        "id" => "SIGN",
        "content" => Loc::getMessage("TR_CA_DOCS_COL_SIGN"),
        "sort" => "SIGN",
        "default" => true
    ),
    array(
        "id" => "TYPE",
        "content" => Loc::getMessage("TR_CA_DOCS_COL_TYPE"),
        "sort" => "TYPE",
        "default" => true
    ),
));

while ($arRes = $rsData->NavNext(true, "f_")) {

    $doc = Docs\Database::getDocumentById($f_ID);
    $docId = $doc->getId();

    $docName = "<input type='button' value='i' onclick='verify([";
    $docName .= $docId . "])' class='verify_button' title='" . Loc::getMessage("TR_CA_DOCS_VERIFY_DOC") . "'/>";
    $docName .= "<a class='tn_document' title='" . Loc::getMessage("TR_CA_DOCS_DOWNLOAD_DOC") . "' onclick='self.download([";
    $docName .= $docId . "], true)' >" . $doc->getName() . "</a>";

    if ($doc->getSigners() == "") {
        $signers = array();
    } else {
        $signers = $doc->getSignersToArray();
    }
    $signersString = '<table width=100%>';

    foreach ($signers as $key => $signer) {

        $signingTime = Loc::getMessage("TR_CA_DOCS_SIGN_TIME")
            . date("d:m:o H:i", round($signer[signingTime] / 1000));

        $subjectName = Loc::getMessage("TR_CA_DOCS_SIGN_NAME");
        if ($signer[subject_name]) $subjectName .= $signer[subject_name];
        else $subjectName .= $signer[subjectFriendlyName];

        if ($signer[subjectName][O])
            $subjectCompany = Loc::getMessage("TR_CA_DOCS_SIGN_ORG") . $signer[subjectName][O];

        $signersString .= "<tr><td>" . $signingTime . "</td><td>" . $subjectName . "</td><td>" . $subjectCompany . "</td></tr>";
    }
    $signersString .= '</table>';

    $arId = array();
    $arId[] = $doc->getId();

    $docCreated = $doc->getCreated();
    $docParentCreated = $doc->getFirstParent()->getCreated();

    $docType = $doc->getType();
    $docTypeString = Docs\Utils::GetTypeString($doc);

    $docStatus = $doc->getStatus();
    if ($docStatus !== DOC_STATUS_NONE) {
        $docTypeString .=
            "<br>" .
            Loc::getMessage("TR_CA_DOCS_STATUS") .
            Docs\Utils::GetStatusString($doc);
    }

    $arRes = array(
        "DOC" => $doc->getId(),
        "FILE_NAME" => $docName,
        "PARENT_CREATED" => $docParentCreated,
        "CREATED" => $docCreated,
        "SIGN" => $signersString,
        "TYPE" => $docType,
    );

    $row = &$lAdmin->AddRow($f_ID, $arRes);

    $row->AddViewField("DOC", $doc->getId());
    $row->AddViewField("FILE_NAME", $docName);
    $row->AddViewField("PARENT_CREATED", $docParentCreated);
    $row->AddViewField("CREATED", $docCreated);
    $row->AddViewField("SIGN", $signersString);
    $row->AddViewField("TYPE", $docTypeString);

    // context menu
    $arActions = Array();

    // add sign action for docs without "blocked" status
    if ($docStatus !== DOC_STATUS_BLOCKED) {
        $arActions[] = array(
            "ICON" => "edit",
            "DEFAULT" => true,
            "TEXT" => Loc::getMessage("TR_CA_DOCS_ACT_SIGN"),
            "ACTION" => "sign(" . json_encode($arId) . ")"
        );
        $arActions[] = array("SEPARATOR" => true);
    }

    // add unblock action for docs with status PROCESSING
    if ($docStatus === DOC_STATUS_BLOCKED) {
        $arActions[] = array(
            "ICON" => "access",
            "DEFAULT" => false,
            "TEXT" => Loc::getMessage("TR_CA_DOCS_ACT_UNBLOCK"),
            "ACTION" => "unblock(" . json_encode($arId) . ")"
        );
        $arActions[] = array("SEPARATOR" => true);
    }

    $arActions[] = array(
        "ICON" => "delete",
        "DEFAULT" => false,
        "TEXT" => Loc::getMessage("TR_CA_DOCS_ACT_REMOVE"),
        "ACTION" => "remove(" . json_encode($arId) . ")"
    );

    $arActions[] = array("SEPARATOR" => true);

    // remove separator if it is the last item
    if (is_set($arActions[count($arActions) - 1], "SEPARATOR"))
        unset($arActions[count($arActions) - 1]);

    // apply context menu to the row
    $row->AddActions($arActions);
}

$lAdmin->AddFooter(array(
    array(
        "title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
        "value" => $rsData->SelectedRowsCount()
    ),
    array(
        "counter" => true,
        "title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
        "value" => "0"
    )
)
);

$lAdmin->AddGroupActionTable(Array(
    "sign" => Loc::getMessage("TR_CA_DOCS_ACT_SIGN"),
    "unblock" => Loc::getMessage("TR_CA_DOCS_ACT_UNBLOCK"),
    "remove" => Loc::getMessage("TR_CA_DOCS_ACT_REMOVE"),
)
);

$contextMenu = array(
    array(
        "ICON" => "btn_new",
        "TEXT" => Loc::getMessage("TR_CA_DOCS_ADD_DOC"),
        "TITLE" => Loc::getMessage("TR_CA_DOCS_ADD_DOC"),
        "LINK" => "trusted_cryptoarm_docs_upload.php?lang=" . LANGUAGE_ID,
    )
);
$lAdmin->AddAdminContextMenu($contextMenu);

// alternative output - ajax or excel
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("TR_CA_DOCS_TITLE"));

// separates preparing of data and output
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter($sTableID . "_filter", array(
    Loc::getMessage("TR_CA_DOCS_COL_ID"),
    Loc::getMessage("TR_CA_DOCS_COL_FILENAME"),
    Loc::getMessage("TR_CA_DOCS_COL_SIGN"),
    Loc::getMessage("TR_CA_DOCS_COL_TYPE"),
    Loc::getMessage("TR_CA_DOCS_COL_STATUS")
));
?>

<?php
if (!Docs\Utils::isSecure()) {
    echo BeginNote(), Loc::getMessage("TM_DOCS_MODULE_HTTP_WARNING"), EndNote();
}
?>

<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage() ?>">

    <?php $oFilter->Begin(); ?>

    <tr>
        <td> <?= Loc::getMessage("TR_CA_DOCS_COL_ID") . ":" ?></td>
        <td><input type="text" name="find_docId" size="47" value="<?= htmlspecialchars($find_docId) ?>"></td>
    </tr>

    <tr>
        <td> <?= Loc::getMessage("TR_CA_DOCS_COL_FILENAME") . ":" ?></td>
        <td><input type="text" name="find_fileName" size="47" value="<?= htmlspecialchars($find_fileName) ?>"></td>
    </tr>

    <tr>
        <td> <?= Loc::getMessage("TR_CA_DOCS_COL_SIGN") . ":" ?></td>
        <td><input type="text" name="find_signInfo" size="47" value="<?= htmlspecialchars($find_signInfo) ?>"></td>
    </tr>

    <tr>
        <td> <?= Loc::getMessage("TR_CA_DOCS_COL_TYPE") . ":" ?> </td>
        <td>
            <?php
            $arr = array(
                "reference_id" => array(
                    "",
                    DOC_TYPE_FILE,
                    DOC_TYPE_SIGNED_FILE,
                ),
                "reference" => array(
                    "",
                    Loc::getMessage("TR_CA_DOCS_TYPE_" . DOC_TYPE_FILE),
                    Loc::getMessage("TR_CA_DOCS_TYPE_" . DOC_TYPE_SIGNED_FILE),
                ),
            );
            echo SelectBoxFromArray("find_type", $arr, $find_type, Loc::getMessage("POST_ALL"), "");
            ?>
        </td>

    <tr>
        <td> <?= Loc::getMessage("TR_CA_DOCS_COL_STATUS") . ":" ?> </td>
        <td>
            <?php
            $arr = array(
                "reference_id" => array(
                    "",
                    DOC_STATUS_BLOCKED,
                    DOC_STATUS_CANCELED,
                    DOC_STATUS_ERROR,
                ),
                "reference" => array(
                    "",
                    Loc::getMessage("TR_CA_DOCS_STATUS_" . DOC_STATUS_BLOCKED),
                    Loc::getMessage("TR_CA_DOCS_STATUS_" . DOC_STATUS_CANCELED),
                    Loc::getMessage("TR_CA_DOCS_STATUS_" . DOC_STATUS_ERROR),
                ),
            );
            echo SelectBoxFromArray("find_status", $arr, $find_status, Loc::getMessage("POST_ALL"), "");
            ?>
        </td>
    </tr>

    <?php
    $oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(), "form" => "find_form"));
    $oFilter->End();
    ?>

</form>

<?php $lAdmin->DisplayList(); ?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>

