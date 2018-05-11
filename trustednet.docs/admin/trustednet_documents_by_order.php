<?php
use TrustedNet\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$module_id = "trustednet.docs";
Loader::includeModule($module_id);
Loc::loadMessages(__FILE__);

// Do not show page if module sale is unavailable
if (!ModuleManager::isModuleInstalled("sale")) {
    echo "SALE_MODULE_NOT_INSTALLED";
    require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
    die();
}
Loader::includeModule("sale");

// current user rights for the module
$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);

$MAIL_EVENT_ID = Option::get($module_id, "MAIL_EVENT_ID", "");
$MAIL_TEMPLATE_ID = Option::get($module_id, "MAIL_TEMPLATE_ID", "");

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
        $orders = Docs\Database::getOrdersByFilter(array($by => $order), $arFilter);
        while ($order = $orders->Fetch()) {
            $arOrders[] = $order["ORDER"];
        }
        $ids = array();
        foreach ($arOrders as $order) {
            $idsOrder = Docs\Database::getIdsByOrder($order);
            foreach ($idsOrder as $id) {
                $ids[] = $id;
            }
        }
    } else {
        foreach ($arID as $ID) {
            $ID = IntVal($ID);
            $idsOrder = Docs\Database::getIdsByOrder($ID);
            foreach ($idsOrder as $id) {
                $ids[] = $id;
            }
        }
    }

    switch ($_REQUEST['action']) {
        case "sign":
            echo '<script>';
            echo 'window.parent.sign(' . json_encode($ids) . ', {"role": "SELLER"})';
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
        case "send_mail":
            if (!$MAIL_EVENT_ID || !$MAIL_TEMPLATE_ID) {
                echo "<script>alert('" . Loc::getMessage("TN_DOCS_MAIL_NOT_CONFIGURED") . "')</script>";
                break;
            }
            $i = 0;
            $e = 0;
            $eventEmailSent = Option::get($module_id, "EVENT_EMAIL_SENT", "");

            foreach ($ids as $id) {

                $order_ID = Docs\Database::getOrderByDocumentId($id);
                $order_ID = implode($order_ID);
                $order = CSaleOrder::GetByID(intval($order_ID));
                $user_id = $order["USER_ID"];
                $user = CUser::GetByID($user_id)->Fetch();
                $user_email = $user["EMAIL"];
                $user_name = $user["NAME"];

                $doc = Docs\Database::getDocumentById($id);
                $docLink = urldecode($_SERVER['DOCUMENT_ROOT'] . $doc->getHtmlPath());

                $sites = CSite::GetList($by = "sort", $order = "asc", array("ACTIVE" => "Y"));
                $siteIds = array();
                while ($site = $sites->Fetch()) {
                    $siteIds[] = $site["ID"];
                }
                $arEventFields = array(
                    "EMAIL" => $user_email,
                    "ORDER_USER" => $user_name,
                    "ORDER_ID" => $order_ID,
                    "FILE_NAME" => $doc->getName(),
                    "SITE_URL" => "http://" . $_SERVER["HTTP_HOST"],
                );

                // Create archive with the document file
                $archivePath = $_SERVER["DOCUMENT_ROOT"] . "/" . $doc->getName() . ".zip";
                $archiveObject = CBXArchive::GetArchive($archivePath);
                $archiveObject->SetOptions(
                    array(
                        "REMOVE_PATH" => $_SERVER["DOCUMENT_ROOT"] . dirname($doc->getPath()),
                    )
                );
                $archiveObject->Pack(array($docLink));

                if (CEvent::Send($MAIL_EVENT_ID, $siteIds, $arEventFields, "N", $MAIL_TEMPLATE_ID, array($archivePath))) {
                    $i++;

                    if ($eventEmailSent) {
                        Docs\DocumentsByOrder::changeOrderStatus($doc, $eventEmailSent);
                    }

                    // Add email tracking property
                    $docProps = $doc->getProperties();
                    if ($emailProp = $docProps->getPropByType("EMAIL")) {
                        $emailProp->setValue("SENT");
                    } else {
                        $docProps->add(new Docs\Property($id, "EMAIL", "SENT"));
                    }
                    $doc->save();

                    Docs\Utils::log(array(
                        "action" => "email_sent",
                        "docs" => $doc,
                    ));
                } else {
                    $e++;
                };

                // Remove temporary archive file
                File::deleteFile($archivePath);
            }
            $message = Loc::getMessage("TN_DOCS_MAIL_SENT_PRE") . $i . Loc::getMessage("TN_DOCS_MAIL_SENT_POST");
            echo "<script>alert('" . $message . "')</script>";
            if ($e > 0) {
                $message = Loc::getMessage("TN_DOCS_MAIL_ERROR_PRE") . $e . Loc::getMessage("TN_DOCS_MAIL_ERROR_POST");
                echo "<script>alert('" . $message . "')</script>";
            }
            // Reload page to show changed order status
            if ($eventEmailSent) {
                echo "<script>location.reload()</script>";
            }
            break;
    }
}

$orders = Docs\Database::getOrdersByFilter(array($by => $order), $arFilter);

// convert list to the CAdminResult class
$rsData = new CAdminResult($orders, $sTableID);

// page-by-page navigation
$rsData->NavStart();

// send page selector to the main object $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage("TN_DOCS_NAV_TEXT")));

$lAdmin->AddHeaders(array(
    array(
        "id" => "ORDER",
        "content" => Loc::getMessage("TN_DOCS_COL_ORDER"),
        "sort" => "ORDER",
        "default" => true,
    ),
    array(
        "id" => "ORDER_STATUS",
        "content" => Loc::getMessage("TN_DOCS_COL_ORDER_STATUS"),
        "sort" => "ORDER_STATUS",
        "default" => true,
    ),
    array(
        "id" => "BUYER",
        "content" => Loc::getMessage("TN_DOCS_COL_BUYER"),
        "sort" => "CLIENT_NAME",
        "default" => true,
    ),
    array(
        "id" => "DOCS",
        "content" => Loc::getMessage("TN_DOCS_COL_DOCS"),
        "default" => true,
    ),
));

while ($arRes = $rsData->NavNext(true, "f_")) {

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
    $docs = Docs\Database::getDocumentsByOrder($order_id);
    $docList = $docs->getList();

    $orderViewField = "[<a href='";
    $orderViewField .= "/bitrix/admin/sale_order_edit.php?ID=" . $order_id . "'";
    $orderViewField .= "title='" . Loc::getMessage("TN_DOCS_EDIT_ORDER") . "'>";
    $orderViewField .= $order_id;
    $orderViewField .= "</a>]";

    $buyerViewField = $user_name . " " . $user_last_name . "<br />";
    $buyerViewField .= "[<a href='";
    $buyerViewField .= "/bitrix/admin/user_edit.php?ID=" . $user_id . "'";
    $buyerViewField .= "title='" . Loc::getMessage("TN_DOCS_BUYER_PROFILE") . "'>";
    $buyerViewField .= $user_login;
    $buyerViewField .= "</a>]<br />";
    $buyerViewField .= "<small><a href='mailto:";
    $buyerViewField .= $user_email;
    $buyerViewField .= "' title='" . Loc::getMessage("TN_DOCS_MAILTO") . "'>";
    $buyerViewField .= $user_email;
    $buyerViewField .= "</a></small>";

    $docViewField = "<table class='trustednetdocs_doc_table'>";
    foreach ($docList as $doc) {
        $docId = $doc->getId();
        $docName = $doc->getName();
        $docEmailProp = $doc->getProperties()->getPropByType("EMAIL");
        $docEmailIcon = '<img src="/bitrix/themes/.default/icons/trustednet.docs/email_not_sent.png"';
        $docEmailIcon .= ' class="email_icon" title="' . Loc::getMessage("TN_DOCS_EMAIL_NOT_SENT") . '">';
        if ($docEmailProp) {
            $docEmailPropValue = $docEmailProp->getValue();
            if ($docEmailPropValue == "SENT") {
                $docEmailIcon = '<img src="/bitrix/themes/.default/icons/trustednet.docs/email_sent.png"';
                $docEmailIcon .= ' class="email_icon" title="' . Loc::getMessage("TN_DOCS_EMAIL_SENT") . '">';
            }
            if ($docEmailPropValue == "READ") {
                $docEmailIcon = '<img src="/bitrix/themes/.default/icons/trustednet.docs/email_read.png"';
                $docEmailIcon .= ' class="email_icon" title="' . Loc::getMessage("TN_DOCS_EMAIL_READ") . '">';
            }
        }
        $docRoleStatus = Docs\DocumentsByOrder::getRoleString($doc);
        if ($doc->getStatus() == DOC_STATUS_NONE) {
            $docStatus = "";
        } else {
            $docStatus = "<b>" . Loc::getMessage("TN_DOCS_STATUS") . "</b> " . Docs\Utils::getStatusString($doc);
        }
        $docViewField .= "<tr>";
        $docViewField .= "<td>";
        $docViewField .= "<input class='verify_button' type='button' value='i' onclick='verify([";
        $docViewField .= $docId . "])' title='" . Loc::getMessage("TN_DOCS_VERIFY_DOC") . "'/>";
        $docViewField .= $docEmailIcon;
        $docViewField .= "<a class='tn_document' title='" . Loc::getMessage("TN_DOCS_DOWNLOAD_DOC") . "' onclick='self.download(";
        $docViewField .= $docId;
        $docViewField .= ", true)'>";
        $docViewField .= $docName . "</a>";
        $docViewField .= "</td>";
        $docViewField .= "<td>" . $docRoleStatus . "<br />";
        $docViewField .= $docStatus . "</td>";
        $docViewField .= "</tr>";
    }
    $docViewField .= "</table>";

    $row->AddViewField("ORDER", $orderViewField);
    $row->AddViewField("ORDER_STATUS", $order_status);
    $row->AddViewField("BUYER", $buyerViewField);
    $row->AddViewField("DOCS", "<small>" . $docViewField . "</small>");

    // context menu
    $arActions = array();

    // Add sign action for orders with unblocked docs
    foreach ($docList as &$doc) {
        if ($doc->getStatus() !== DOC_STATUS_BLOCKED) {

            $arActions[] = array(
                "ICON" => "edit",
                "DEFAULT" => true,
                "TEXT" => Loc::getMessage("TN_DOCS_ACT_SIGN"),
                //"ACTION" => "sign(" . json_encode($ids) . ")"
                "ACTION" => $lAdmin->ActionDoGroup($f_ID, "sign"),
            );

            $arActions[] = array("SEPARATOR" => true);

            break;
        }
    }

    // Add unblock action for orders with blocked docs
    foreach ($docList as &$doc) {
        if ($doc->getStatus() == DOC_STATUS_BLOCKED) {

            $arActions[] = array(
                "ICON" => "access",
                "DEFAULT" => false,
                "TEXT" => Loc::getMessage("TN_DOCS_ACT_UNBLOCK"),
                "ACTION" => $lAdmin->ActionDoGroup($f_ID, "unblock"),
            );

            $arActions[] = array("SEPARATOR" => true);

            break;
        }
    }

    $arActions[] = array(
        "ICON" => "move",
        "DEFAULT" => false,
        "TEXT" => Loc::getMessage("TN_DOCS_ACT_SEND_MAIL"),
        "ACTION" => $lAdmin->ActionDoGroup($f_ID, "send_mail"),
    );

    $arActions[] = array("SEPARATOR" => true);

    $arActions[] = array(
        "ICON" => "delete",
        "DEFAULT" => false,
        "TEXT" => Loc::getMessage("TN_DOCS_ACT_REMOVE"),
        "ACTION" => $lAdmin->ActionDoGroup($f_ID, "remove"),
    );

    // apply context menu to the row
    $row->AddActions($arActions);

}

$lAdmin->AddFooter(
    array(
        array(
            "title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
            "value" => $rsData->SelectedRowsCount()
        ),
        array(
            "counter" => true,
            "title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
            "value" => "0"
        ),
    )
);

$lAdmin->AddGroupActionTable(Array(
    "sign" => Loc::getMessage("TN_DOCS_ACT_SIGN"),
    "unblock" => Loc::getMessage("TN_DOCS_ACT_UNBLOCK"),
    "send_mail" => Loc::getMessage("TN_DOCS_ACT_SEND_MAIL"),
    "remove" => Loc::getMessage("TN_DOCS_ACT_REMOVE"),
));

$contextMenu = array(
    array(
        "ICON" => "btn_new",
        "TEXT" => Loc::getMessage("TN_DOCS_ADD_DOC_BY_ORDER"),
        "TITLE" => Loc::getMessage("TN_DOCS_ADD_DOC_BY_ORDER"),
        "LINK" => "trustednet_documents_upload_by_order.php?lang=ru",
    )
);
$lAdmin->AddAdminContextMenu($contextMenu);

// alternative output - ajax or excel
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("TN_DOCS_TITLE"));

// separates preparing of data and output
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
    $sTableID . "_filter", array(
        Loc::getMessage("TN_DOCS_COL_ORDER"),
        Loc::getMessage("TN_DOCS_COL_ORDER_STATUS"),
        Loc::getMessage("TN_DOCS_FILTER_BUYER_EMAIL"),
        Loc::getMessage("TN_DOCS_FILTER_BUYER_NAME"),
        Loc::getMessage("TN_DOCS_FILTER_BUYER_LAST_NAME"),
        Loc::getMessage("TN_DOCS_COL_STATUS")
    )
);
?>

<form name="find_form" method="get" action="<?= $APPLICATION->GetCurPage() ?>">
    <?php $oFilter->Begin(); ?>
    <tr>
        <td><?= Loc::getMessage("TN_DOCS_COL_ORDER") . ":" ?></td>
        <td>
            <input type="text" name="find_order" size="47" value="<?= htmlspecialchars($find_order) ?>">
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage("TN_DOCS_COL_ORDER_STATUS") . ":" ?>  </td>
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

            echo SelectBoxFromArray("find_order_status", $arr, $find_order_status, Loc::getMessage("POST_ALL"), "");
            ?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage("TN_DOCS_FILTER_BUYER_EMAIL") . ":" ?></td>
        <td>
            <input type="text" name="find_clientEmail" size="47" value="<?= htmlspecialchars($find_clientEmail) ?>">
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage("TN_DOCS_FILTER_BUYER_NAME") . ":" ?></td>
        <td>
            <input type="text" name="find_clientName" size="47" value="<?= htmlspecialchars($find_clientName) ?>">
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage("TN_DOCS_FILTER_BUYER_LAST_NAME") . ":" ?></td>
        <td>
            <input type="text" name="find_clientLastName" size="47"
                   value="<?= htmlspecialchars($find_clientLastName) ?>">
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage("TN_DOCS_COL_STATUS") . ":" ?>  </td>
        <td>
            <?php
            $arr = array(
                "reference" => array(
                    "",
                    Loc::getMessage("TN_DOCS_ROLES_CLIENT"),
                    Loc::getMessage("TN_DOCS_ROLES_SELLER"),
                    Loc::getMessage("TN_DOCS_ROLES_BOTH"),
                    Loc::getMessage("TN_DOCS_ROLES_NONE"),
                ),
                "reference_id" => array(
                    "",
                    "CLIENT",
                    "SELLER",
                    "BOTH",
                    "NONE",
                )
            );
            echo SelectBoxFromArray("find_docState", $arr, $find_docState, Loc::getMessage("POST_ALL"), "");
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

