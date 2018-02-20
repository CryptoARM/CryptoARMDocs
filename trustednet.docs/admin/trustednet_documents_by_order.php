<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
CUtil::InitJSCore(array("jquery2"));
$APPLICATION->AddHeadScript("/bitrix/js/trustednet.sign/sign.js");
$module_id = 'trustednet.sign';
CModule::IncludeModule($module_id);

IncludeModuleLangFile(__FILE__);

// current user rights for the module
$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);

$sTableID = "Order_ID";
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

$FilterArr = Array(
    "find",
    "find_order",
    "find_order_status",
    "find_clientEmail",
    "find_clientName",
    "find_clientLastName",
    "find_docState"
);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter()) {
    $arFilter = Array(
        "ID" => ($find != "" && $find_type == "id" ? $find : $find_id),
        "ORDER" => $find_order,
        "ORDER_STATUS" => $find_order_status,
        "CLIENT_EMAIL" => $find_clientEmail,
        "CLIENT_NAME" => $find_clientName,
        "CLIENT_LASTNAME" => $find_clientLastName,
        "DOC_STATE" => $find_docState
    );
}


if (($arID = $lAdmin->GroupAction()) && $POST_RIGHT == "W") {

    // selected = checkbox "for all"
    if ($_REQUEST['action_target'] == 'selected') {
        $orders = getOrdersByFilter(array($by => $order), $arFilter);
        while ($order = $orders->Fetch()) {
            $arOrders[] = $order["ORDER"];
        }
        $ids = array();
        foreach ($arOrders as $order) {
            $idsOrder = getIdsByOrder($order);
            foreach ($idsOrder as $id) {
                $ids[] = $id;
            }
        }
    } else {
        // each list element
        foreach ($arID as $ID) {
            $ID = IntVal($ID);
            $idsOrder = getIdsByOrder($ID);
            foreach ($idsOrder as $id) {
                $ids[] = $id;
            }
        }
    }

    switch ($_REQUEST['action']) {
        case "sign":
            echo '<script>';
            echo 'window.parent.signOrAlert(' . json_encode($ids) . ', "SELLER")';
            echo '</script>';
            break;
        case "unblock":
            echo '<script>';
            echo 'window.parent.unblock(' . json_encode($ids) . ')';
            echo '</script>';
            break;
        case "send_mail":
            $i = 0;
            $e = 0;

            foreach ($ids as $id) {

                $order_ID = getOrderByDocunent($id);
                $order_ID = implode($order_ID);
                $order = CSaleOrder::GetByID(intval($order_ID));
                $user_id = $order["USER_ID"];
                $user = CUser::GetByID($user_id)->Fetch();
                $user_email = $user["EMAIL"];
                $user_name = $user["NAME"];

                $html = TDataBaseDocument::getDocumentById($id);
                $link = urldecode($_SERVER['DOCUMENT_ROOT'] . $html->getHtmlPath());

                $arEventFields = array(
                    "EMAIL" => $user_email,
                    "ORDER_USER" => $user_name,
                );
                if (CEvent::Send("manual_signed", "s4", $arEventFields, "N", "", array($link))) {
                    $i++;
                } else {
                    $e++;
                };
            }
            $message = GetMessage("TRUSTEDNETSIGNER_MAIL_SENT_PRE") . $i . GetMessage("TRUSTEDNETSIGNER_MAIL_SENT_POST");
            echo "<script>alert('" . $message . "')</script>";
            if ($e > 0) {
                $message = GetMessage("TRUSTEDNETSIGNER_MAIL_ERROR_PRE") . $e . GetMessage("TRUSTEDNETSIGNER_MAIL_SENT_POST");
                echo "<script>alert('" . $message . "')</script>}}";
            }
            break;
    }
}

$orders = getOrdersByFilter(array($by => $order), $arFilter);

// convert list to the CAdminResult class
$rsData = new CAdminResult($orders, $sTableID);

// page-by-page navigation
$rsData->NavStart();

// send page selector to the main object $lAdmin
$lAdmin->NavText("<p class='nav_print' style='text-align: center;'>" . $rsData->GetNavPrint(GetMessage("TRUSTEDNETSIGNER_ORDER")) . "</p>");

$lAdmin->AddHeaders(array(
    array("id" => "ORDER", "content" => GetMessage("TRUSTEDNETSIGNER_DOCS_ORDER"), "sort" => "ORDER", "default" => true),
    array("id" => "ORDER_STATUS", "content" => GetMessage("TRUSTEDNETSIGNER_DOCS_ORDER_STATUS"), "sort" => "ORDER_STATUS", "default" => true),
    array("id" => "BUYER", "content" => GetMessage("TRUSTEDNETSIGNER_DOCS_BUYER"), "sort" => "CLIENT_NAME", "default" => true),
    array("id" => "DOCS", "content" => GetMessage('TRUSTEDNETSIGNER_DOCS_GEN'), "sort" => "DOCS", "default" => true),
    array("id" => "SIGN", "content" => GetMessage('TRUSTEDNETSIGNER_DOCS_STATUS'), "sort" => "DOC_STATE", "default" => true),
));

while ($arRes = $rsData->NavNext(true, "f_")):

    $f_ID = $f_ORDER;

    $row = &$lAdmin->AddRow($f_ID, $arRes);

    // get order
    $order = CSaleOrder::GetByID(intval($f_ID));
    $order_id = $order["ID"];
    if (!$order_id) {
        continue;
    }
    $user_id = $order["USER_ID"];

    // get order status
    $order_status_id = $order["STATUS_ID"];
    $arStatus = CSaleStatus::GetByID($order_status_id);
    $order_status = $arStatus["NAME"];

    // get order user
    $user = CUser::GetByID($user_id)->Fetch();
    $user_name = $user["NAME"];
    $user_last_name = $user["LAST_NAME"];
    $user_email = $user["EMAIL"];
    $user_login = $user["LOGIN"];

    // get docs by order
    $docs = getDocumentsByOrder($order_id);

    // order
    $arActions = Array();
    $fieldOrder = "[<a href=\"/bitrix/admin/sale_order_edit.php?ID=" . $order_id . "&lang=" . LANG . "\" title=\"" . GetMessage("TRUSTEDNETSIGNER_USER_PROFILE") . "\">" . $order_id . "</a>] ";

    // client
    $fieldValue = "[<a href=\"/bitrix/admin/user_edit.php?ID=" . $user_id . "&lang=" . LANG . "\" title=\"" . GetMessage("TRUSTEDNETSIGNER_USER_PROFILE") . "\">" . $user_name . "</a>] ";
    $fieldValue .= htmlspecialcharsEx($user_name . ((strlen($user_name) <= 0 || strlen($user_last_name) <= 0) ? "" : " ") . $user_last_name) . "<br/>";
    $fieldValue .= htmlspecialcharsEx($user_login) . "<br/>";
    $fieldValue .= "<small><a href=\"mailto:" . htmlspecialcharsEx($user_email) . "\" title=\"" . GetMessage("STA_MAILTO") . "\">" . htmlspecialcharsEx($user_email) . "</a></small>";

    // docs/status
    $html_docs = '';
    $array = $docs->getList();
    $html_signs = '';
    foreach ($array as &$doc) {
        $doc = $doc->getLastDocument();
        $html_docs .= '<input type="button" value="i" onclick="view(' . $doc->getId() . ')" style="float: left; font-style: italic; margin: 2px; width: 15px;  margin-right: 10px; height: 15px; padding: 0;"/>';
        $html_docs .= '<a class="tn-document" style="cursor: pointer;" onclick="downloadOrAlert(' . $doc->getId() . ')" data-id="' . $doc->getId() . '" >' . $doc->getName() . '</a> <br/><br/>';
        $status = $doc->getStatus();
        $str = "";
        if ($status && $status->getValue() == DOCUMENT_STATUS_PROCESSING) {
            $str = GetMessage("TRUSTEDNETSIGNER_DOC_BLOCKED");
        } else {
            $str = getStateString($doc);
        }
        $html_signs .= $str . '<br/><br/>';
    }

    $row->AddViewField("ORDER", $fieldOrder);
    $row->AddViewField("ORDER_STATUS", $order_status);
    $row->AddViewField("BUYER", $fieldValue);
    $row->AddViewField("DOCS", '<small>' . $html_docs . '<small>');
    $row->AddViewField("SIGN", '<small>' . $html_signs . '<small>');

    // context menu
    $arActions = Array();

    $arActions[] = array(
        "ICON" => "edit",
        "DEFAULT" => true,
        "TEXT" => GetMessage("TRUSTEDNETSIGNER_SIGN_ACTION"),
        //"ACTION" => "sign(" . json_encode($ids) . ")"
        "ACTION" => $lAdmin->ActionDoGroup($f_ID, "sign"),
    );

    $arActions[] = array("SEPARATOR" => true);

    // Add unblock action for docs with status PROCESSING
    $blockedDocs = false;
    foreach ($array as &$doc) {
        if ($doc->getStatus() && $doc->getStatus()->getValue() == DOCUMENT_STATUS_PROCESSING) {
            $blockedDocs = true;
        }
    }
    if ($blockedDocs) {
        $arActions[] = array(
            "ICON" => "access",
            "DEFAULT" => false,
            "TEXT" => GetMessage("TRUSTEDNETSIGNER_UNBLOCK_ACTION"),
            "ACTION" => $lAdmin->ActionDoGroup($f_ID, "unblock"),
        );

        $arActions[] = array("SEPARATOR" => true);
    }

    $arActions[] = array(
        "ICON" => "move",
        "DEFAULT" => false,
        "TEXT" => GetMessage("TRUSTEDNETSIGNER_SEND_MAIL_ACTION"),
        "ACTION" => $lAdmin->ActionDoGroup($f_ID, "send_mail"),
    );

    // apply context menu to the row
    $row->AddActions($arActions);

endwhile;

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

$lAdmin->AddGroupActionTable(Array(
    "sign" => GetMessage("MAIN_ADMIN_LIST_SIGN"),
    "unblock" => GetMessage("MAIN_ADMIN_LIST_UNBLOCK"),
    "send_mail" => GetMessage("MAIN_ADMIN_LIST_SEND_MAIL"),
));

$lAdmin->AddAdminContextMenu();

// alternative output - ajax or excel
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("TRUSTEDNETSIGNER_TITLE"));

// separates preparing of data and output
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
    $sTableID . "_filter", array(
        GetMessage("find_order"),
        GetMessage("find_order_status"),
        GetMessage("find_clientEmail"),
        GetMessage("find_clientName"),
        GetMessage("find_clientLastName"),
        GetMessage("find_docState")
    )
);
?>

<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage() ?>">
    <?php $oFilter->Begin(); ?>
    <tr>
        <td><?= GetMessage("find_order") . ":" ?></td>
        <td>
            <input type="text" name="find_order" size="47" value="<?= htmlspecialchars($find_order) ?>">
        </td>
    </tr>
    <tr>
        <td><?= GetMessage("find_order_status") . ":" ?>  </td>
        <td>
            <?php
            $res = CSaleStatus::GetList(array("SORT" => "ASC"), array("LID" => LANGUAGE_ID), false, false, array('ID', 'NAME'));
            $arr_ref = array("");
            $arr_ref_id = array("");

            while ($arFields = $res->Fetch()) {
                $arr_ref[] = $arFields["NAME"];
                $arr_ref_id[] = $arFields["ID"];
            }

            $arr = array(
                "reference" => $arr_ref,
                "reference_id" => $arr_ref_id
            );

            echo SelectBoxFromArray("find_order_status", $arr, $find_order_status, GetMessage("POST_ALL"), "");
            ?>
        </td>
    </tr>
    <tr>
        <td><?= GetMessage("find_clientEmail") . ":" ?></td>
        <td>
            <input type="text" name="find_clientEmail" size="47" value="<?= htmlspecialchars($find_clientEmail) ?>">
        </td>
    </tr>
    <tr>
        <td><?= GetMessage("find_clientName") . ":" ?></td>
        <td>
            <input type="text" name="find_clientName" size="47" value="<?= htmlspecialchars($find_clientName) ?>">
        </td>
    </tr>
    <tr>
        <td><?= GetMessage("find_clientLastName") . ":" ?></td>
        <td>
            <input type="text" name="find_clientLastName" size="47"
                   value="<?= htmlspecialchars($find_clientLastName) ?>">
        </td>
    </tr>
    <tr>
        <td><?= GetMessage("find_docState") . ":" ?>  </td>
        <td>
            <?php
            $arr = array(
                "reference" => array(
                    "",
                    GetMessage("STATE_CLIENT"),
                    GetMessage("STATE_SELLER"),
                    GetMessage("STATE_BOTH"),
                    GetMessage("STATE_NONE"),
                ),
                "reference_id" => array(
                    "",
                    "CLIENT",
                    "SELLER",
                    "BOTH",
                    "NONE",
                )
            );
            echo SelectBoxFromArray("find_docState", $arr, $find_docState, GetMessage("POST_ALL"), "");
            ?>
        </td>
    </tr>

    <?php
    $oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(), "form" => "find_form"));
    $oFilter->End();
    ?>

</form>

<?
CModule::IncludeModule("trustednet.auth");
$token = OAuth2::getFromSession();
if ($token) {
    ?>

    <?php
    $lAdmin->DisplayList();
    ?>

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

<? } else {
    $APPLICATION->IncludeComponent(
        "trustednet:trustednet.auth", "",
        Array()
    );
} ?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>

