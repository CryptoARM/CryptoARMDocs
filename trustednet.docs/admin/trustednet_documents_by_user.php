<?php
use TrustedNet\Docs;

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php";
$module_id = "trustednet.docs";
CModule::IncludeModule($module_id);

IncludeModuleLangFile(__FILE__);

// current user rights for the module
$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);

$sTableID = "User_ID";
$oSort = new CAdminSorting($sTableID, 'SORT', 'asc');
// main list object
$lAdmin = new CAdminList($sTableID, $oSort);

function CheckFilter()
{
    global $FilterArr, $lAdmin;
    foreach ($FilterArr as $f)
        global $$f;
    // return false on errors
    return count($lAdmin->arFilterErrors) == 0;
}

$FilterArr = array(
    "find_user_id",
    "find_user_name",
    "find_user_email",
    "find_doc_id",
    "find_doc_name",
    "find_doc_type",
    "find_doc_status",
);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter()) {
    $arFilter = array(
        "USER_ID" => $find_user_id,
        "USER_NAME" => $find_user_name,
        "USER_EMAIL" => $find_user_email,
        "DOC_ID" => $find_doc_id,
        "DOC_NAME" => $find_doc_name,
        "DOC_TYPE" => $find_doc_type,
        "DOC_STATUS" => $find_doc_status,
    );
}


if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {

    // selected = checkbox "for all"
    if ($_REQUEST['action_target'] == 'selected') {
        $users = Docs\Database::getUsersWithDocsByFilter($by, $order, $arFilter);
        while ($user = $users->Fetch()) {
            $arUsers[] = $user["ID"];
        }
        $ids = array();
        foreach ($arUsers as $user) {
            $idsUser = Docs\Database::getDocumentIdsByUser($user);
            foreach ($idsUser as $id) {
                $ids[] = $id;
            }
        }
    } else {
        foreach ($arID as $ID) {
            $ID = IntVal($ID);
            $idsUser = Docs\Database::getDocumentIdsByUser($ID);
            foreach ($idsUser as $id) {
                $ids[] = $id;
            }
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

$users = Docs\Database::getUsersWithDocsByFilter($by, $order, $arFilter);

// convert list to the CAdminResult class
$rsData = new CAdminResult($users, $sTableID);

// page-by-page navigation
$rsData->NavStart();

// send page selector to the main object $lAdmin
$lAdmin->NavText("<p>" . $rsData->GetNavPrint(GetMessage("TN_DOCS_NAV_TEXT")) . "</p>");

$lAdmin->AddHeaders(
    array(
        array(
            "id" => "USER_ID",
            "content" => GetMessage("TN_DOCS_COL_USER_ID"),
            "sort" => "USER_ID",
            "default" => true,
        ),
        array(
            "id" => "USER_NAME",
            "content" => GetMessage("TN_DOCS_FIELDS_USER_NAME"),
            "sort" => "USER_NAME",
            "default" => true,
        ),
        array(
            "id" => "DOCS",
            "content" => GetMessage("TN_DOCS_COL_DOCS"),
            "default" => true,
        ),
    )
);

while ($arRes = $rsData->NavNext(true, "f_")) {

    $row = &$lAdmin->AddRow($f_ID, $arRes);

    $docs = Docs\Database::getDocumentsByUser($f_ID);
    $docList = $docs->getList();

    $userNameViewField = $f_NAME . "<br />";
    $userNameViewField .= "[<a href='/bitrix/admin/user_edit.php?ID=" . $f_ID . "'>";
    $userNameViewField .= $f_LOGIN;
    $userNameViewField .= "</a>]<br />";
    $userNameViewField .= "<a href='mailto:" . $f_EMAIL . "'>" . $f_EMAIL . "</a>";

    $docViewField = "<table class='trustednetdocs_doc_table'>";
    foreach ($docList as $doc) {
        $docId = $doc->getId();
        $docName = $doc->getName();
        $docType = Docs\Utils::getTypeString($doc);
        if ($doc->getStatus() === DOC_STATUS_NONE) {
            $docStatus = "";
        } else {
            $docStatus = "<b>" . GetMessage("TN_DOCS_DOC_STATUS") . "</b> " . Docs\Utils::getStatusString($doc);
        }
        $docViewField .= "<tr>";
        $docViewField .= "<td>";
        $docViewField .= "<input class='verify_button' type='button' value='i' onclick='verify([" . $docId . "])'/>";
        $docViewField .= "<a class='tn_document' onclick='self.download(" . $docId . ", true)'>" . $docName . "</a>";
        $docViewField .= "</td>";
        $docViewField .= "<td>" . $docType . "<br />";
        $docViewField .= $docStatus . "</td>";
        $docViewField .= "</tr>";
    }
    $docViewField .= "</table>";

    $row->AddViewField("USER_ID", $f_ID);
    $row->AddViewField("USER_NAME", $userNameViewField);
    $row->AddViewField("DOCS", $docViewField);

    // context menu
    $arActions = array();

    // Add sign action for users with unblocked docs
    foreach ($docList as &$doc) {
        if ($doc->getStatus() !== DOC_STATUS_BLOCKED) {

            $arActions[] = array(
                "ICON" => "edit",
                "DEFAULT" => true,
                "TEXT" => GetMessage("TN_DOCS_ACT_SIGN"),
                "ACTION" => $lAdmin->ActionDoGroup($f_ID, "sign"),
            );

            $arActions[] = array("SEPARATOR" => true);

            break;
        }
    }

    // Add unblock action for users with blocked docs
    foreach ($docList as &$doc) {
        if ($doc->getStatus() == DOC_STATUS_BLOCKED) {

            $arActions[] = array(
                "ICON" => "access",
                "DEFAULT" => false,
                "TEXT" => GetMessage("TN_DOCS_ACT_UNBLOCK"),
                "ACTION" => $lAdmin->ActionDoGroup($f_ID, "unblock"),
            );

            $arActions[] = array("SEPARATOR" => true);

            break;
        }
    }

    $arActions[] = array(
        "ICON" => "delete",
        "DEFAULT" => false,
        "TEXT" => GetMessage("TN_DOCS_ACT_REMOVE"),
        "ACTION" => $lAdmin->ActionDoGroup($f_ID, "remove"),
    );

    // apply context menu to the row
    $row->AddActions($arActions);

}

$lAdmin->AddFooter(
    array(
        array(
            "title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
            "value" => $rsData->SelectedRowsCount()
        ),
        array(
            "counter" => true,
            "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
            "value" => "0"
        ),
    )
);

$lAdmin->AddGroupActionTable(
    array(
        "sign" => GetMessage("TN_DOCS_ACT_SIGN"),
        "unblock" => GetMessage("TN_DOCS_ACT_UNBLOCK"),
        "remove" => GetMessage("TN_DOCS_ACT_REMOVE"),
    )
);

$contextMenu = array(
    array(
        "ICON" => "btn_new",
        "TEXT" => GetMessage("TN_DOCS_ADD_DOC_BY_USER"),
        "TITLE" => GetMessage("TN_DOCS_ADD_DOC_BY_USER"),
        "LINK" => "trustednet_documents_upload_by_user.php?lang=ru",
    )
);
$lAdmin->AddAdminContextMenu($contextMenu);

// alternative output - ajax or excel
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("TN_DOCS_TITLE"));

// separates preparing of data and output
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
    $sTableID . "_filter",
    array(
        GetMessage("TN_DOCS_FIELDS_USER_ID"),
        GetMessage("TN_DOCS_FIELDS_USER_NAME"),
        GetMessage("TN_DOCS_FIELDS_USER_EMAIL"),
        GetMessage("TN_DOCS_FIELDS_DOC_ID"),
        GetMessage("TN_DOCS_FIELDS_DOC_NAME"),
        GetMessage("TN_DOCS_FIELDS_DOC_TYPE"),
        GetMessage("TN_DOCS_FIELDS_DOC_STATUS"),
    )
);
?>

<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage() ?>">
    <?php $oFilter->Begin(); ?>

    <tr>
        <td>
            <?= GetMessage("TN_DOCS_FIELDS_USER_ID") . ":" ?>
        </td>
        <td>
            <input type="text" name="find_user_id" size="47" value="<?= htmlspecialchars($find_user_id) ?>">
        </td>
    </tr>

    <tr>
        <td>
            <?= GetMessage("TN_DOCS_FIELDS_USER_NAME") . ":" ?>
        </td>
        <td>
            <input type="text" name="find_user_name" size="47" value="<?= htmlspecialchars($find_user_name) ?>">
        </td>
    </tr>

    <tr>
        <td>
            <?= GetMessage("TN_DOCS_FIELDS_USER_EMAIL") . ":" ?>
        </td>
        <td>
            <input type="text" name="find_user_email" size="47" value="<?= htmlspecialchars($find_user_email) ?>">
        </td>
    </tr>

    <tr>
        <td>
            <?= GetMessage("TN_DOCS_FIELDS_DOC_ID") . ":" ?>
        </td>
        <td>
            <input type="text" name="find_doc_id" size="47" value="<?= htmlspecialchars($find_doc_id) ?>">
        </td>
    </tr>

    <tr>
        <td>
            <?= GetMessage("TN_DOCS_FIELDS_DOC_NAME") . ":" ?>
        </td>
        <td>
            <input type="text" name="find_doc_name" size="47" value="<?= htmlspecialchars($find_doc_name) ?>">
        </td>
    </tr>

    <tr>
        <td>
            <?= GetMessage("TN_DOCS_FIELDS_DOC_TYPE") . ":" ?>
        </td>
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
                    GetMessage("TN_DOCS_TYPE_" . DOC_TYPE_FILE),
                    GetMessage("TN_DOCS_TYPE_" . DOC_TYPE_SIGNED_FILE),
                ),
            );
            echo SelectBoxFromArray("find_doc_type", $arr, $find_doc_type, GetMessage("POST_ALL"), "");
            ?>
        </td>
    </tr>

    <tr>
        <td>
            <?= GetMessage("TN_DOCS_FIELDS_DOC_STATUS") . ":" ?>
        </td>
        <td>
            <?php
            $arr = array(
                "reference_id" => array(
                    "",
                    DOC_STATUS_BLOCKED,
                    DOC_STATUS_CANCEL,
                    DOC_STATUS_ERROR,
                ),
                "reference" => array(
                    "",
                    GetMessage("TN_DOCS_STATUS_" . DOC_STATUS_BLOCKED),
                    GetMessage("TN_DOCS_STATUS_" . DOC_STATUS_CANCEL),
                    GetMessage("TN_DOCS_STATUS_" . DOC_STATUS_ERROR),
                ),
            );
            echo SelectBoxFromArray("find_doc_status", $arr, $find_doc_status, GetMessage("POST_ALL"), "");
            ?>
        </td>
    </tr>

    <?php
    $oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(), "form" => "find_form"));
    $oFilter->End();
    ?>

</form>

<?
$lAdmin->DisplayList();
?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>

