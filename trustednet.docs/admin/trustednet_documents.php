<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php";
CUtil::InitJSCore(array("jquery2"));
//require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/include.php");

$module_id = "trustednet.docs";
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

    $docs = new DocumentCollection();
    foreach($ids as $id) {
        $doc = TDataBaseDocument::getDocumentById($id);
        $docs->add($doc);
    }

    switch ($_REQUEST['action']) {
        case "sign":
            echo '<script>';
            echo 'window.parent.sign(' . $docs->toJSON() . ', null, true)';
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
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("TN_DOCS_TITLE")));

$lAdmin->AddHeaders(array(
    array(
        "id" => "DOC",
        "content" => GetMessage("TN_DOCS_COL_ID"),
        "sort" => "DOC",
        "default" => true
    ),
    array(
        "id" => "FILE_NAME",
        "content" => GetMessage("TN_DOCS_COL_FILENAME"),
        "sort" => "FILE_NAME",
        "default" => true
    ),
    array(
        "id" => "SIGN",
        "content" => GetMessage("TN_DOCS_COL_SIGN"),
        "sort" => "SIGN",
        "default" => true
    ),
    array(
        "id" => "STATUS",
        "content" => GetMessage("TN_DOCS_COL_STATUS"),
        "sort" => "STATUS",
        "default" => true
    )
));

while ($arRes = $rsData->NavNext(true, "f_")) {

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

        $signingTime = GetMessage("TN_DOCS_SIGN_TIME")
            . date("d:m:o H:i", round($signer[signingTime] / 1000));

        $subjectName = GetMessage("TN_DOCS_SIGN_NAME");
        if ($signer[subject_name]) $subjectName .= $signer[subject_name];
        else $subjectName .= $signer[subjectFriendlyName];

        if ($signer[subjectName][O])
            $subjectCompany = GetMessage("TN_DOCS_SIGN_ORG") . $signer[subjectName][O];

        $signersString .= "<tr><td>" . $signingTime . "</td><td>" . $subjectName . "</td><td>" . $subjectCompany . "</td></tr>";
    }
    $signersString .= '</table>';

    $arId = array();
    $arId[] = $doc->getId();

    $docStatus = $doc->getStatus($doc);
    if ($docStatus) {
        $docStatus = $docStatus->getValue();
        $docStatusString = GetMessage("TN_DOCS_STATUS_" . $docStatus);
    } else {
        $docStatusString = GetMessage("TN_DOCS_STATUS_NONE");
    }

    $arRes = array(
        "DOC" => $doc->getId(),
        "FILE_NAME" => $docName,
        "SIGN" => $signersString,
        "STATUS" => $docStatusString,
    );

    $row = &$lAdmin->AddRow($f_ID, $arRes);

    $row->AddViewField("DOC", $doc->getId());
    $row->AddViewField("FILE_NAME", $docName);
    $row->AddViewField("SIGN", $signersString);
    $row->AddViewField("STATUS", $docStatusString);

    $docColl = new DocumentCollection();
    $docColl->add($doc);

    // context menu
    $arActions = Array();

    // add sign action for docs without status
    if (!$docStatus) {
        $arActions[] = array(
            "ICON" => "edit",
            "DEFAULT" => true,
            "TEXT" => GetMessage("TN_DOCS_ACT_SIGN"),
            "ACTION" => "sign(" . $docColl->toJSON() . ", null)"
        );
    } else {
        // add unblock action for docs with status PROCESSING
        if ($docStatus == DOC_STATUS_PROCESSING) {
            $arActions[] = array(
                "ICON" => "access",
                "DEFAULT" => false,
                "TEXT" => GetMessage("TN_DOCS_ACT_UNBLOCK"),
                "ACTION" => "unblock(" . json_encode($arId) . ")"
            );
        }
    }

    $arActions[] = array("SEPARATOR" => true);

    $arActions[] = array(
        "ICON" => "delete",
        "DEFAULT" => false,
        "TEXT" => GetMessage("TN_DOCS_ACT_REMOVE"),
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
    "sign" => GetMessage("TN_DOCS_ACT_SIGN"),
    "unblock" => GetMessage("TN_DOCS_ACT_UNBLOCK"),
    "remove" => GetMessage("TN_DOCS_ACT_REMOVE"),
)
);

$contextMenu = array(
    array(
        "ICON" => "btn_new",
        "TEXT" => GetMessage("TN_DOCS_ADD_DOC"),
        "TITLE" => GetMessage("TN_DOCS_ADD_DOC"),
        "LINK" => "trustednet_documents_upload.php?lang=ru"
    )
);
$lAdmin->AddAdminContextMenu($contextMenu);

// alternative output - ajax or excel
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("TN_DOCS_TITLE"));

// separates preparing of data and output
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter($sTableID . "_filter", array(
    GetMessage("TN_DOCS_COL_ID"),
    GetMessage("TN_DOCS_COL_FILENAME"),
    GetMessage("TN_DOCS_COL_SIGN"),
    GetMessage("TN_DOCS_COL_STATUS")
));
?>

<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage() ?>">

    <?php $oFilter->Begin(); ?>

    <tr>
        <td> <?= GetMessage("TN_DOCS_COL_ID") . ":" ?></td>
        <td><input type="text" name="find_docId" size="47" value="<?= htmlspecialchars($find_docId) ?>"></td>
    </tr>

    <tr>
        <td> <?= GetMessage("TN_DOCS_COL_FILENAME") . ":" ?></td>
        <td><input type="text" name="find_fileName" size="47" value="<?= htmlspecialchars($find_fileName) ?>"></td>
    </tr>

    <tr>
        <td> <?= GetMessage("TN_DOCS_COL_SIGN") . ":" ?></td>
        <td><input type="text" name="find_signInfo" size="47" value="<?= htmlspecialchars($find_signInfo) ?>"></td>
    </tr>

    <tr>
        <td> <?= GetMessage("TN_DOCS_COL_STATUS") . ":" ?> </td>
        <td>
            <?php
            $arr = array(
                "reference_id" => array("", "1", "2", "3"),
                "reference" => array("",
                    GetMessage("TN_DOCS_STATUS_1"),
                    GetMessage("TN_DOCS_STATUS_2"),
                    GetMessage("TN_DOCS_STATUS_3")
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

<?php $lAdmin->DisplayList(); ?>

<script>
    // send php variables to the js
    var TN_DOCS_AJAX_CONTROLLER = "<?= TN_DOCS_AJAX_CONTROLLER ?>";
    var TN_ALERT_NO_CLIENT = "<?= GetMessage('TN_ALERT_NO_CLIENT') ?>";
    var TN_ALERT_DOC_NOT_FOUND = "<?= GetMessage('TN_ALERT_DOC_NOT_FOUND') ?>";
    var TN_ALERT_DOC_BLOCKED = "<?= GetMessage('TN_ALERT_DOC_BLOCKED') ?>";
    var TN_ALERT_REMOVE_ACTION_CONFIRM = "<?= GetMessage('TN_ALERT_REMOVE_ACTION_CONFIRM') ?>";
    var TN_ALERT_LOST_DOC_REMOVE_CONFIRM_PRE = "<?= GetMessage('TN_ALERT_LOST_DOC_REMOVE_CONFIRM_PRE') ?>";
    var TN_ALERT_LOST_DOC_REMOVE_CONFIRM_POST = "<?= GetMessage('TN_ALERT_LOST_DOC_REMOVE_CONFIRM_POST') ?>";
    var TN_ALERT_LOST_DOC = "<?= GetMessage('TN_ALERT_LOST_DOC') ?>";
</script>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>

