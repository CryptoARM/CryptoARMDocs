<?php
use TrustedNet\Docs;

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php";
//require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/include.php");

if (!$USER->CanDoOperation('fileman_upload_files')) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

CModule::IncludeModule("fileman");

$module_id = "trustednet.docs";
CModule::IncludeModule($module_id);
IncludeModuleLangFile(__FILE__);

$addUrl = 'lang=' . LANGUAGE_ID . ($logical == "Y" ? '&logical=Y' : '');

$strWarning = "";

$io = CBXVirtualIo::GetInstance();

$DOCUMENTS_DIR = COption::GetOptionString($module_id, "DOCUMENTS_DIR", "/docs/");
$DOCUMENTS_DIR = $io->CombinePath("/", $DOCUMENTS_DIR);

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$ret = "/bitrix/admin/trustednet_documents_by_user.php?lang=" . LANGUAGE_ID;
$sub = $_SERVER[REQUEST_URI];

$bCan = false;

if ($REQUEST_METHOD == "POST" && strlen($save) > 0 && check_bitrix_sessid()) {
    // Check permissions
    if (!$USER->CanDoFileOperation('fm_upload_file', $arPath)) {
        $strWarning = GetMessage("ACCESS_DENIED");
    } else {
        $bCan = true;
        $nums = IntVal($nums);
        if ($nums > 0) {
            for ($i = 1; $i <= $nums; $i++) {
                $path = $_POST["dir_" . $i];
                $arFile = $_FILES["file_" . $i];
                $arUserId = $_POST["user_id_" . $i];
                // User-set property value can contain spaces,
                // but not at the beginning or end of the string
                // Spaces only value will be ignored
                $arUserId = trim($arUserId);

                if (strlen($arFile["name"]) <= 0 || $arFile["tmp_name"] == "none") continue;

                $arFile["name"] = CFileman::GetFileName($arFile["name"]);
                $filename = ${"filename_" . $i};
                if (strlen($filename) <= 0) $filename = $arFile["name"];

                // Add subfolder with unique id
                $uniqid = strval(uniqid());
                $uniqpath = $io->CombinePath($path, "/", $uniqid);

                $pathto = Rel2Abs($uniqpath, $filename);
                if (!$USER->CanDoFileOperation('fm_upload_file', Array($site, $pathto)))
                    $strWarning .= GetMessage("TN_DOCS_UPLOAD_ACCESS_DENIED") . " \"" . $pathto . "\"\n";
                elseif ($arFile["error"] == 1 || $arFile["error"] == 2)
                    $strWarning .= GetMessage("TN_DOCS_UPLOAD_SIZE_ERROR", Array('#FILE_NAME#' => $pathto)) . "\n";
                elseif (($mess = CFileMan::CheckFileName(str_replace('/', '', $pathto))) !== true)
                    $strWarning .= $mess . ".\n";
                elseif ($io->FileExists($DOC_ROOT . $pathto))
                    $strWarning .= GetMessage("TN_DOCS_UPLOAD_FILE_EXISTS1") . " \"" . $pathto . "\" " . GetMessage("TN_DOCS_UPLOAD_FILE_EXISTS2") . ".\n";
                elseif (!$USER->IsAdmin() && (HasScriptExtension($pathto) || substr(CFileman::GetFileName($pathto), 0, 1) == "."))
                    $strWarning .= GetMessage("TN_DOCS_UPLOAD_PHPERROR") . " \"" . $pathto . "\".\n";
                elseif (!Docs\Utils::propertyNumericalIdValidation($arUserId))
                    $strWarning .= GetMessage("TN_DOCS_UPLOAD_INVALID_USER_ID") . "\n";
                elseif (!CUser::GetByID((int)$arUserId)->Fetch())
                    $strWarning .= GetMessage("TN_DOCS_UPLOAD_USER_ID_DOESNT_EXIST");
                elseif (preg_match("/^\/bitrix\/.*/", $pathto))
                    $strWarning .= GetMessage("TN_DOCS_UPLOAD_INVALID_DIR");
                else {
                    $bQuota = true;
                    if (COption::GetOptionInt("main", "disk_space") > 0) {
                        $f = $io->GetFile($arFile["tmp_name"]);
                        $bQuota = false;
                        $size = $f->GetFileSize();
                        $quota = new CDiskQuota();
                        if ($quota->checkDiskQuota(array("FILE_SIZE" => $size))) $bQuota = true;
                    }

                    if ($bQuota) {
                        if (!$io->Copy($arFile["tmp_name"], $DOC_ROOT . $pathto)) {
                            $strWarning .= GetMessage("TN_DOCS_UPLOAD_FILE_CREATE_ERROR") . " \"" . $pathto . "\". ";
                            $strWarning .= GetMessage("TN_DOCS_UPLOAD_FILE_CREATE_ERROR_NO_ACCESS") . "\n";
                        } else {
                            if (COption::GetOptionInt("main", "disk_space") > 0)
                                CDiskQuota::updateDiskQuota("file", $size, "copy");
                            $f = $io->GetFile($DOC_ROOT . $pathto);
                            $f->MarkWritable();
                            if (COption::GetOptionString($module_id, "log_page", "Y") == "Y") {
                                $res_log['path'] = substr($pathto, 1);
                                CEventLog::Log("content", "FILE_ADD", "main", "", serialize($res_log));
                            }
                            if (Docs\Utils::createDocument($pathto, "USER", $arUserId));
                            else $strWarning .= 'Error creating file';
                        }
                    } else $strWarning .= $quota->LAST_ERROR . "\n";
                }
            }
        }

        if (strlen($strWarning) <= 0) {
            $backurl = '/bitrix/admin/trustednet_documents_by_user.php?lang=' . LANGUAGE_ID;
            if (!empty($_POST["apply"])) LocalRedirect($ret); else LocalRedirect($ret);
        }
    }
}

$APPLICATION->SetTitle(GetMessage("TN_DOCS_UPLOAD_TITLE"));
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php";
?>

<? CAdminMessage::ShowMessage($strWarning); ?>

<?=
    CAdminFileDialog::ShowScript
    (
        Array(
            "event" => "dirSelector",
            "arResultDest" => Array("FUNCTION_NAME" => "dirSelectorAct"),
            "arPath" => Array(),
            "select" => 'D',// F - file only, D - folder only
            "operation" => 'O',
            "showUploadTab" => false,
            "showAddToMenuTab" => false,
            "fileFilter" => '',
            "allowAllFiles" => true,
            "SaveConfig" => true
        )
    );
?>

<script>
var selectedId;

function dirSelectorWrapper(i)
{
    selectedId = 'dir_' + i;
    dirSelector();
}

function dirSelectorAct(filename, path, site)
{
    var dirInput = document.getElementById(selectedId);
    selectedId = null;
    dirInput.value = path;
}
</script>

<? if (strlen($strWarning) <= 0 || $bCan): ?>

    <style>
    .adm-workarea .adm-input-file{
        text-overflow: ellipsis;
        width: 115px;
    }
    </style>

    <form method="POST"
          action="<?= $APPLICATION->GetCurPage() . "?" . $addUrl . "&site=" . $site . "&path=" . UrlEncode($path) ?>"
          name="ffilemanupload" enctype="multipart/form-data">
        <input type="hidden" name="logical" value="<?= htmlspecialcharsbx($logical) ?>">
        <?= GetFilterHiddens("filter_"); ?>
        <input type="hidden" name="save" value="Y">

        <?= bitrix_sessid_post(); ?>

        <?
        $aTabs = array(array("DIV" => "edit1", "TAB" => GetMessage("TN_DOCS_UPLOAD_TAB_TITLE"), "ICON" => "fileman", "TITLE" => ''),);
        $tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
        $tabControl->Begin();
        $tabControl->BeginNextTab();
        ?>

        <tr>
            <td colspan="2" align="left">
                <input type="hidden" name="nums" value="5">
                <table id="bx-upload-tbl">
                    <tr class="heading">

                        <td></td>

                        <td style="text-align: left!important;">
                            <?= GetMessage("TN_DOCS_UPLOAD_FILE_USER_ID") ?>
                            <span class="required"><sup>*</sup></span>
                        </td>

                        <td style="text-align: left!important;">
                            <?= GetMessage("TN_DOCS_UPLOAD_FILE_DIR") ?>
                        </td>

                    </tr>
                    <? for ($i = 1; $i <= 5; $i++): ?>
                        <tr>

                            <td class="adm-detail-content-cell-l">
                                <input type="file" name="file_<?= $i ?>" size="30"
                                       maxlength="255" value="">
                            </td>

                            <td class="adm-detail-content-cell-r">
                                <input type="text" name="user_id_<?= $i ?>"
                                       placeholder=""
                                       autocomplete="off"
                                       size="15" maxlength="255" value="">
                            </td>

                            <td class="adm-detail-content-cell-l; white-space: nowrap;">
                                <div style="white-space: nowrap;">
                                    <input class="adm_input" id="dir_<?= $i ?>" name="dir_<?= $i ?>"
                                           value="<?= $DOCUMENTS_DIR ?>" style="width:220px;opacity:0.7;cursor:pointer;"
                                           onclick="dirSelectorWrapper(<?= $i ?>)" type="text" readonly/>
                                </div>
                            </td>

                        </tr>
                    <? endfor ?>
                </table>
            </td>
        </tr>
        <?
        $tabControl->EndTab();
        $tabControl->Buttons(
            array(
                "btnApply" => false,
                "disabled" => false,
                // "back_url" => "/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path)
                "back_url" => $ret // $sub = "/bitrix/admin/trustednet_docs.php"
            )
        );
        $tabControl->End();
        ?>
    </form>

    <?echo BeginNote();?>
    <span class="required"><sup>*</sup></span><?echo GetMessage("TN_DOCS_UPLOAD_USER_ID_NOTE")?>
    <?echo EndNote();?>

<? endif; ?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>
