<?php
use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Application;

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php";
//require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/include.php");

$app = Application::getInstance();
$context = $app->getContext();
$docRoot = $context->getServer()->getDocumentRoot();

if (!$USER->CanDoOperation('fileman_upload_files')) {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

Loader::includeModule("fileman");

// Do not show page if module sale is unavailable
if (!ModuleManager::isModuleInstalled("sale")) {
    echo "SALE_MODULE_NOT_INSTALLED";
    require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
    die();
}
Loader::includeModule("sale");

$module_id = "trusted.cryptoarmdocs";
Loader::includeModule($module_id);
Loc::loadMessages($docRoot . "/bitrix/modules/" . $module_id . "/admin/trusted_cryptoarm_docs_upload.php");

$addUrl = 'lang=' . LANGUAGE_ID . ($logical == "Y" ? '&logical=Y' : '');

$strWarning = "";

$io = CBXVirtualIo::GetInstance();

$DOCUMENTS_DIR = COption::GetOptionString($module_id, "DOCUMENTS_DIR", "/docs/");
$DOCUMENTS_DIR = $io->CombinePath("/", $DOCUMENTS_DIR);

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$ret = "/bitrix/admin/trusted_cryptoarm_docs_by_order.php?lang=" . LANGUAGE_ID;
$sub = $_SERVER[REQUEST_URI];

$bCan = false;

if ($REQUEST_METHOD == "POST") {
    // Check permissions
    if (!$USER->CanDoFileOperation('fm_upload_file', $arPath)) {
        $strWarning = Loc::getMessage("ACCESS_DENIED");
    } elseif (strlen($save) === 0) {
        $bCan = true;
        $strWarning .= Loc::getMessage("TR_CA_DOCS_UPLOAD_ERROR");
    } elseif (strlen($save) > 0 && check_bitrix_sessid()) {
        $bCan = true;
        $nums = IntVal($nums);
        if ($nums > 0) {
            for ($i = 1; $i <= $nums; $i++) {
                $path = $_POST["dir_" . $i];
                $arFile = $_FILES["file_" . $i];
                $arOrderId = $_POST["order_id_" . $i];
                // User-set property value can contain spaces,
                // but not at the beginning or end of the string
                // Spaces only value will be ignored
                $arOrderId = trim($arOrderId);

                if (strlen($arFile["name"]) <= 0 || $arFile["tmp_name"] == "none") continue;

                $arFile["name"] = CFileman::GetFileName($arFile["name"]);
                $filename = ${"filename_" . $i};
                if (strlen($filename) <= 0) $filename = $arFile["name"];

                // Add subfolder with unique id
                $uniqid = strval(uniqid());
                $uniqpath = $io->CombinePath($path, "/", $uniqid);

                $pathto = Rel2Abs($uniqpath, $filename);
                if (!$USER->CanDoFileOperation('fm_upload_file', Array($site, $pathto)))
                    $strWarning .= Loc::getMessage("TR_CA_DOCS_UPLOAD_ACCESS_DENIED") . " \"" . $pathto . "\"\n";
                elseif ($arFile["error"] == 1 || $arFile["error"] == 2)
                    $strWarning .= Loc::getMessage("TR_CA_DOCS_UPLOAD_SIZE_ERROR", Array('#FILE_NAME#' => $pathto)) . "\n";
                elseif (!count($_FILES))
                    $strWarning .= Loc::getMessage("TR_CA_DOCS_UPLOAD_ERROR") . "\n";
                elseif (($mess = CFileMan::CheckFileName(str_replace('/', '', $pathto))) !== true)
                    $strWarning .= $mess . ".\n";
                elseif ($io->FileExists($DOC_ROOT . $pathto))
                    $strWarning .= Loc::getMessage("TR_CA_DOCS_UPLOAD_FILE_EXISTS1") . " \"" . $pathto . "\" " . Loc::getMessage("TR_CA_DOCS_UPLOAD_FILE_EXISTS2") . ".\n";
                elseif (!$USER->IsAdmin() && (HasScriptExtension($pathto) || substr(CFileman::GetFileName($pathto), 0, 1) == "."))
                    $strWarning .= Loc::getMessage("TR_CA_DOCS_UPLOAD_PHPERROR") . " \"" . $pathto . "\".\n";
                elseif (!Docs\Utils::propertyNumericalIdValidation($arOrderId))
                    $strWarning .= Loc::getMessage("TR_CA_DOCS_UPLOAD_INVALID_ORDER_ID") . "\n";
                elseif (!CSaleOrder::GetByID((int)$arOrderId))
                    $strWarning .= Loc::getMessage("TR_CA_DOCS_UPLOAD_ORDER_ID_DOESNT_EXIST");
                elseif (preg_match("/^\/bitrix\/.*/", $pathto))
                    $strWarning .= Loc::getMessage("TR_CA_DOCS_UPLOAD_INVALID_DIR");
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
                            $strWarning .= Loc::getMessage("TR_CA_DOCS_UPLOAD_FILE_CREATE_ERROR") . " \"" . $pathto . "\". ";
                            $strWarning .= Loc::getMessage("TR_CA_DOCS_UPLOAD_FILE_CREATE_ERROR_NO_ACCESS") . "\n";
                        } else {
                            if (COption::GetOptionInt("main", "disk_space") > 0)
                                CDiskQuota::updateDiskQuota("file", $size, "copy");
                            $f = $io->GetFile($DOC_ROOT . $pathto);
                            $f->MarkWritable();
                            if (COption::GetOptionString($module_id, "log_page", "Y") == "Y") {
                                $res_log['path'] = substr($pathto, 1);
                                CEventLog::Log("content", "FILE_ADD", "main", "", serialize($res_log));
                            }
                            $orderUser = CSaleOrder::GetByID((int)$arOrderId)["USER_ID"];
                            $props = new Docs\PropertyCollection();
                            $props->add(new Docs\Property("ORDER", (string)$arOrderId));
                            $props->add(new Docs\Property("ROLES", "NONE"));
                            $props->add(new Docs\Property("USER", (string)$orderUser));
                            if (Docs\Utils::createDocument($pathto, $props));
                            else $strWarning .= 'Error creating file';
                        }
                    } else $strWarning .= $quota->LAST_ERROR . "\n";
                }
            }
        }

        if (strlen($strWarning) <= 0) {
            $backurl = '/bitrix/admin/trusted_cryptoarm_docs_by_order.php?lang=' . LANGUAGE_ID;
            if (!empty($_POST["apply"])) LocalRedirect($ret); else LocalRedirect($ret);
        }
    }
}

$APPLICATION->SetTitle(Loc::getMessage("TR_CA_DOCS_UPLOAD_BY_ORDER_TITLE"));
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

    <form method="POST"
          action="<?= $APPLICATION->GetCurPage() . "?" . $addUrl . "&site=" . $site . "&path=" . UrlEncode($path) ?>"
          name="ffilemanupload" enctype="multipart/form-data">
        <input type="hidden" name="logical" value="<?= htmlspecialcharsbx($logical) ?>">
        <?= GetFilterHiddens("filter_"); ?>
        <input type="hidden" name="save" value="Y">

        <?= bitrix_sessid_post(); ?>

        <?
        $aTabs = array(array("DIV" => "edit1", "TAB" => Loc::getMessage("TR_CA_DOCS_UPLOAD_TAB_TITLE"), "ICON" => "fileman", "TITLE" => ''),);
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
                            <?= Loc::getMessage("TR_CA_DOCS_UPLOAD_FILE_ORDER_ID") ?>
                            <span class="required"><sup>*</sup></span>
                        </td>

                        <td style="text-align: left!important;">
                            <?= Loc::getMessage("TR_CA_DOCS_UPLOAD_FILE_DIR") ?>
                        </td>

                    </tr>
                    <?
                    $maxSize  = Docs\Utils::maxUploadFileSize();
                    $sizeFileJS = "trustedCA.checkFileSize(this.files[0], $maxSize, null, () => { this.value = null; event.stopImmediatePropagation() })";
                    ?>
                    <? for ($i = 1; $i <= 5; $i++):
                        $onFailure = "() => { this.value = null; let element = document.getElementById('trca-adm-input-file_$i');";
                        $onFailure .= " element.childNodes[1].childNodes[0].innerHTML = '" . Loc::getMessage("TR_CA_DOCS_UPLOAD_ADD_FILE") . "' }";
                        $accessFileJS = "trustedCA.checkAccessFile(this.files[0], null, $onFailure)";
                        $sizeFileJS = "trustedCA.checkFileSize(this.files[0], $maxSize, $accessFileJS, $onFailure)";
                    ?>
                        <tr>

                            <td class="adm-detail-content-cell-l">
                                <div class="trca-adm-input-file" id="trca-adm-input-file_<?= $i?>">
                                    <input type="file" name="file_<?= $i ?>" size="30"
                                        maxlength="255" value=""
                                        onchange="<?= $sizeFileJS ?>">
                                </div>
                            </td>

                            <td class="adm-detail-content-cell-r">
                                <input type="text" name="order_id_<?= $i ?>"
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
                "back_url" => $ret // $sub = "/bitrix/admin/trusted_cryptoarm_docs.php"
            )
        );
        $tabControl->End();
        ?>
    </form>

    <?echo BeginNote();?>
    <span class="required"><sup>*</sup></span><?echo Loc::getMessage("TR_CA_DOCS_UPLOAD_ORDER_ID_NOTE")?>
    <?echo EndNote();?>

    <?echo BeginNote();?>
    <?echo Loc::getMessage("TR_CA_DOCS_UPLOAD_DOC_MAX_SIZE1") . $maxSize . Loc::getMessage("TR_CA_DOCS_UPLOAD_DOC_MAX_SIZE2")?><br>
    <?echo EndNote();?>

<? endif; ?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>

