<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
CUtil::InitJSCore(array("jquery2"));
$APPLICATION->AddHeadScript("/bitrix/js/trustednet.sign/sign.js");
//require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/include.php");

$module_id = 'trustednet.sign';
CModule::IncludeModule($module_id);
IncludeModuleLangFile(__FILE__);

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
    "find_status"
);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter()) {
    $arFilter = Array(
        "ID" => ($find != "" && $find_type == "id" ? $find : $find_id),
        "DOC" => $find_docId,
        "FILE_NAME" => $find_fileName,
        "SIGN" => $find_signInfo,
        "STATUS" => $find_status
    );
}

if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {
    // selected = checkbox "for all"
    if ($_REQUEST['action_target'] == 'selected') {
        // apply filter
        $docs = TDataBaseDocument::getIdDocumentsByFilter(array($by => $order), $arFilter);
        while($arRes = $docs->Fetch()) {
            $ids[] = $arRes['ID'];
        }
    } else {
        foreach ($arID as $ID) {
            $ID = IntVal($ID);
            $ids[] = $ID;
        }
    }
    switch ($_REQUEST['action']) {
        case "sign":
            echo '<script>';
            echo 'window.parent.signOrAlert(' . json_encode($ids) . ', null, true)';
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

$docs = TDataBaseDocument::getIdDocumentsByFilter(array($by => $order), $arFilter);
$rsData = new CAdminResult($docs, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("TRUSTEDNETSIGNER_DOC_NAVY")));

$lAdmin->AddHeaders(array(
    array(
        "id" => "DOC",
        "content" => GetMessage("TRUSTEDNETSIGNER_DOC"),
        "sort" => "DOC",
        "default" => true
    ),
    array(
        "id" => "FILE_NAME",
        "content" => GetMessage("TRUSTEDNETSIGNER_FILE_NAME"),
        "sort" => "FILE_NAME",
        "default" => true
    ),
    array(
        "id" => "SIGN",
        "content" => GetMessage('TRUSTEDNETSIGNER_SIGN'),
        "sort" => "SIGN",
        "default" => true
    ),
    array(
        "id" => "STATUS",
        "content" => GetMessage('TRUSTEDNETSIGNER_STATUS'),
        "sort" => "STATUS",
        "default" => true
    )
));

while ($arRes = $rsData->NavNext(true, "f_")) :

    $doc = TDataBaseDocument::getDocumentById($f_ID);

    $docName = '<input type="button" value="i" onclick="view(' . $f_ID . ')" style="float: left; font-style: italic; margin: 2px; width: 15px; margin-right: 10px; height: 15px; padding: 0;"/>';
    $docName .= '<a class="tn-document" style="cursor: pointer;" onclick="downloadOrAlert(' . $doc->getId() . ', true)" data-id="' . $doc->getId() . '" >' . $doc->getName() . '</a>';

    if ($doc->getSigners() == "") {
        $signers = array();
    } else {
        $signers = $doc->getSignersToArray();
    }
    $signersString = '<table width=100%>';

    foreach ($signers as $key => $signer) {

        $signingTime = GetMessage("TRUSTEDNETSIGNER_TABLE_TIME")
            . date("d:m:o H:i", round($signer[signingTime] / 1000));

        $subjectName = GetMessage("TRUSTEDNETSIGNER_TABLE_NAME");
        if ($signer[subject_name]) $subjectName .= $signer[subject_name];
        else $subjectName .= $signer[subjectFriendlyName];

        if ($signer[subjectName][O])
            $subjectCompany = GetMessage("TRUSTEDNETSIGNER_TABLE_ORG") . $signer[subjectName][O];

        $signersString .= "<tr><td>" . $signingTime . "</td><td>" . $subjectName . "</td><td>" . $subjectCompany . "</td></tr>";
    }
    $signersString .= '</table>';

    $docStatus = TDataBaseDocument::getStatus($doc);
    if ($docStatus) $docStatus = ($docStatus->getValue());
    else $docStatus = 10;

    $ids = array();
    $ids[] = $doc->getId();

    $arRes = array(
        "DOC" => $doc->getId(),
        "FILE_NAME" => $docName,
        "SIGN" => $signersString,
        "STATUS" => GetMessage("TRUSTEDNETSIGNER_STATUS_" . $docStatus)
    );

    $row = &$lAdmin->AddRow($f_ID, $arRes);

    $row->AddViewField("DOC", $doc->getId());
    $row->AddViewField("FILE_NAME", $docName);
    $row->AddViewField("SIGN", $signersString);
    $row->AddViewField("STATUS", GetMessage("TRUSTEDNETSIGNER_STATUS_" . $docStatus));

    // context menu
    $arActions = Array();

    $arActions[] = array(
        "ICON" => "edit",
        "DEFAULT" => true,
        "TEXT" => GetMessage("TRUSTEDNETSIGNER_SIGN_ACTION"),
        "ACTION" => "signOrAlert(" . json_encode($ids) . ", null, true)"
    );

    $arActions[] = array("SEPARATOR" => true);

    // add unblock action for docs with status PROCESSING
    if ($doc->getStatus() && $doc->getStatus()->getValue() == DOCUMENT_STATUS_PROCESSING) {
        $arActions[] = array(
            "ICON" => "access",
            "DEFAULT" => false,
            "TEXT" => GetMessage("TRUSTEDNETSIGNER_UNBLOCK_ACTION"),
            "ACTION" => "unblock(" . json_encode($ids) . ")"
        );

        $arActions[] = array("SEPARATOR" => true);
    }

    $arActions[] = array(
        "ICON" => "delete",
        "DEFAULT" => false,
        "TEXT" => GetMessage("TRUSTEDNETSIGNER_REMOVE_ACTION"),
        "ACTION" => "remove(" . json_encode($ids) . ")"
    );

    $arActions[] = array("SEPARATOR" => true);

    // remove separator if it is the last item
    if (is_set($arActions[count($arActions) - 1], "SEPARATOR"))
        unset($arActions[count($arActions) - 1]);

    // apply context menu to the row
    $row->AddActions($arActions);
endwhile;

$lAdmin->AddFooter(array(
    array(
        "title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
        "value" => $rsData->SelectedRowsCount()
    ),
    array(
        "counter" => true,
        "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
        "value" => "0"
    )
)
);

$lAdmin->AddGroupActionTable(Array(
    "sign" => GetMessage("MAIN_ADMIN_LIST_SIGN"),
    "unblock" => GetMessage("MAIN_ADMIN_LIST_UNBLOCK"),
    "remove" => GetMessage("MAIN_ADMIN_LIST_REMOVE"),
)
);

$contextMenu = array(
    array(
        "ICON" => "btn_new",
        "TEXT" => GetMessage("TRUSTEDNETSIGNER_FILE_ADD"),
        "TITLE" => GetMessage("TRUSTEDNETSIGNER_FILE_ADD"),
        "LINK" => "trustednet_docs_loading.php?lang=ru"
    )
);
$lAdmin->AddAdminContextMenu($contextMenu);

// alternative output - ajax or excel
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("TRUSTEDNETSIGNER_TITLE"));

// separates preparing of data and output
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter($sTableID . "_filter", array(
    GetMessage("TRUSTEDNETSIGNER_FIND_DOC"),
    GetMessage("TRUSTEDNETSIGNER_FIND_FILE_NAME"),
    GetMessage("TRUSTEDNETSIGNER_FIND_SIGN"),
    GetMessage("TRUSTEDNETSIGNER_FIND_STATUS")
));
?>

<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage() ?>">

    <?php $oFilter->Begin(); ?>

    <tr>
        <td> <?= GetMessage("TRUSTEDNETSIGNER_FIND_DOC") . ":" ?></td>
        <td><input type="text" name="find_docId" size="47" value="<?= htmlspecialchars($find_docId) ?>"></td>
    </tr>

    <tr>
        <td> <?= GetMessage("TRUSTEDNETSIGNER_FIND_FILE_NAME") . ":" ?></td>
        <td><input type="text" name="find_fileName" size="47" value="<?= htmlspecialchars($find_fileName) ?>"></td>
    </tr>

    <tr>
        <td> <?= GetMessage("TRUSTEDNETSIGNER_FIND_SIGN") . ":" ?></td>
        <td><input type="text" name="find_signInfo" size="47" value="<?= htmlspecialchars($find_signInfo) ?>"></td>
    </tr>

    <tr>
        <td> <?= GetMessage("TRUSTEDNETSIGNER_FIND_STATUS") . ":" ?> </td>
        <td>
            <?php
            $arr = array(
                "reference_id" => array("", "1", "2", "3"),
                "reference" => array("",
                    GetMessage("TRUSTEDNETSIGNER_STATUS_1"),
                    GetMessage("TRUSTEDNETSIGNER_STATUS_2"),
                    GetMessage("TRUSTEDNETSIGNER_STATUS_3")
                )
            );
            echo SelectBoxFromArray("find_status", $arr, $find_status, GetMessage("POST_ALL"), "");
            ?>
        </td>
    </tr>

    <?php
    $oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(), "form" => "find_form"));
    $oFilter->End();
    ?>

</form>

<?
$token = OAuth2::getFromSession();
if ($token) {
?>

    <?php $lAdmin->DisplayList(); ?>

    <script>
        // send php variables to the js
        var TRUSTED_URI_COMPONENT_SIGN_AJAX = "<?= TRUSTED_URI_COMPONENT_SIGN_AJAX ?>";
        var TRUSTEDNETSIGNER_DOC_NOT_FOUND = "<?= GetMessage('TRUSTEDNETSIGNER_DOC_NOT_FOUND') ?>";
        var TRUSTEDNETSIGNER_DOC_BLOCKED = "<?= GetMessage('TRUSTEDNETSIGNER_DOC_BLOCKED') ?>";
        var TRUSTEDNETSIGNER_REMOVE_ACTION_CONFIRM = "<?= GetMessage('TRUSTEDNETSIGNER_REMOVE_ACTION_CONFIRM') ?>";
        var TRUSTEDNETSIGNER_DOC_SIGN_NOT_NEEDED = "<?= GetMessage('TRUSTEDNETSIGNER_DOC_SIGN_NOT_NEEDED') ?>";
        var TRUSTEDNETSIGNER_LOST_DOC_REMOVE_CONFIRM_PRE = "<?= GetMessage('TRUSTEDNETSIGNER_LOST_DOC_REMOVE_CONFIRM_PRE') ?>";
        var TRUSTEDNETSIGNER_LOST_DOC_REMOVE_CONFIRM_POST = "<?= GetMessage('TRUSTEDNETSIGNER_LOST_DOC_REMOVE_CONFIRM_POST') ?>";
        var TRUSTEDNETSIGNER_LOST_DOC_ALERT= "<?= GetMessage('TRUSTEDNETSIGNER_LOST_DOC_ALERT') ?>";
    </script>

<?
} else {
    $APPLICATION->IncludeComponent(
        "trustednet:trustednet.auth", "",
        Array()
    );
}
?>


<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>

