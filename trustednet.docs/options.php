<?php
use TrustedNet\Docs;
use Bitrix\Main\Config\Option;

include __DIR__ . "/config.php";
$module_id = TN_DOCS_MODULE_ID;
CModule::IncludeModule($module_id);
$saleModule = CModule::IncludeModule("sale");

IncludeModuleLangFile(__FILE__);

$aTabs = array();
$aTabs[] = array(
    "DIV" => "tn_docs_options",
    "TAB" => GetMessage("TN_DOCS_OPT_TAB"),
    "TITLE" => GetMessage("TN_DOCS_OPT_TAB_TITLE")
);
$aTabs[] = array(
    "DIV" => "tn_docs_license",
    "TAB" => GetMessage("TN_DOCS_LICENSE_TAB"),
    "TITLE" => GetMessage("TN_DOCS_LICENSE_TAB_TITLE")
);
if($saleModule) {
    $aTabs[] = array(
        "DIV" => "tn_docs_email",
        "TAB" => GetMessage("TN_DOCS_EMAIL_TAB"),
        "TITLE" => GetMessage("TN_DOCS_EMAIL_TAB_TITLE")
    );
}
$aTabs[] = array(
    "DIV" => "tn_docs_logs",
    "TAB" => GetMessage("TN_DOCS_LOGS_TAB"),
    "TITLE" => GetMessage("TN_DOCS_LOGS_TAB_TITLE")
);

$tabControl = new CAdminTabControl("trustedTabControl", $aTabs, true, true);

$DOCUMENTS_DIR = Option::get($module_id, 'DOCUMENTS_DIR', "docs");

$PROVIDE_LICENSE = Option::get($module_id, "PROVIDE_LICENSE", "");
$USERNAME = Option::get($module_id, "USERNAME", "");
$PASSWORD = Option::get($module_id, "PASSWORD", "");
$CLIENT_ID = Option::get($module_id, "CLIENT_ID", "");
$SECRET = Option::get($module_id, "SECRET", "");
$MAIL_EVENT_ID = Option::get($module_id, "MAIL_EVENT_ID", "");
$MAIL_SITE_ID = Option::get($module_id, "MAIL_SITE_ID", "");

function TrimDocumentsDir($dir) {
    $dir = trim($dir);
    $dir = trim($dir, "/.");
    // Get rid of /../
    $dir = preg_replace("/\/.*\//", "/", $dir);
    return $dir;
}

function CheckDocumentsDir($dir) {
    $docRoot = $_SERVER["DOCUMENT_ROOT"];
    $fullPath = $docRoot . "/" . $dir;
    // Expand extra /../
    $fullPath = realpath($fullPath);

    if ($dir == '') {
        return GetMessage("TN_DOCS_OPT_EMPTY_DIR_FIELD");
    }

    // Check for existing directory
    if (!is_dir($fullPath)) {
        return GetMessage("TN_DOCS_OPT_NO_DIR");
    }

    // Check if we are in bitrix root
    $len = strlen($docRoot);
    if (strncmp($fullPath, $docRoot, $len) < 0 || strcmp($fullPath, $docRoot) == 0) {
        return GetMessage("TN_DOCS_OPT_CANNOT_USE_SYSTEM_DIRECTORY");
    }

    // Check for entering bitrix system directory
    if (preg_match("/^bitrix($|\/*)/", $dir)) {
        return GetMessage("TN_DOCS_OPT_CANNOT_USE_SYSTEM_DIRECTORY");
    }

    // Check for permissions
    if (is_readable($fullPath) && is_writable($fullPath)) {
        return true;
    } else {
        return GetMessage("TN_DOCS_OPT_NO_ACCESS_TO_DIRECTORY");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid()) {
    if (isset($_POST["Update"])) {
        if (isset($_POST["DOCUMENTS_DIR"])) {
            $documentsDirFromPost = (string)$_POST["DOCUMENTS_DIR"];
        }
        $documentsDirFromPost = TrimDocumentsDir($documentsDirFromPost);
        $checkRes = CheckDocumentsDir($documentsDirFromPost);
        if ($checkRes === true) {
            $DOCUMENTS_DIR = $documentsDirFromPost;
            Option::set($module_id, "DOCUMENTS_DIR", $DOCUMENTS_DIR);
        } else {
            CAdminMessage::ShowMessage($checkRes);
        }
        if (isset($_POST["PROVIDE_LICENSE"])) {
            if (!$_POST["USERNAME"] ||
                !$_POST["PASSWORD"] ||
                !$_POST["CLIENT_ID"] ||
                !$_POST["SECRET"]) {
                CAdminMessage::ShowMessage(GetMessage("TN_DOCS_LICENSE_NO_EMPTY_FIELDS"));
            } else {
                $PROVIDE_LICENSE = (string)$_POST["PROVIDE_LICENSE"];
                Option::set($module_id, "PROVIDE_LICENSE", "on");
                $USERNAME = (string)$_POST["USERNAME"];
                Option::set($module_id, "USERNAME", $USERNAME);
                $PASSWORD = (string)$_POST["PASSWORD"];
                Option::set($module_id, "PASSWORD", $PASSWORD);
                $CLIENT_ID = (string)$_POST["CLIENT_ID"];
                Option::set($module_id, "CLIENT_ID", $CLIENT_ID);
                $SECRET = (string)$_POST["SECRET"];
                Option::set($module_id, "SECRET", $SECRET);
            }
        } else {
            $PROVIDE_LICENSE = false;
            Option::set($module_id, "PROVIDE_LICENSE", "");
        }
        if (isset($_POST["MAIL_EVENT_ID"])) {
            if (trim($_POST["MAIL_EVENT_ID"])) {
                $MAIL_EVENT_ID = (string)$_POST["MAIL_EVENT_ID"];
                Option::set($module_id, "MAIL_EVENT_ID", $MAIL_EVENT_ID);
            }
        }
        if (isset($_POST["MAIL_SITE_ID"])) {
            if (trim($_POST["MAIL_SITE_ID"])) {
                $MAIL_SITE_ID = (string)$_POST["MAIL_SITE_ID"];
                Option::set($module_id, "MAIL_SITE_ID", $MAIL_SITE_ID);
            }
        }
    }
}

$tabControl->Begin();
?>

<form method="POST" enctype="multipart/form-data"
      action="<?= $APPLICATION->GetCurPage() ?>?lang=<?= LANGUAGE_ID ?>&mid=<?= $module_id ?>"
      name="trustednetdocs_settings">

    <?= bitrix_sessid_post(); ?>

    <?= $tabControl->BeginNextTab(); ?>

    <tr>
        <td width="20%" class="adm-detail-content-cell-l">
            <?= GetMessage("TN_DOCS_OPT_DOCS_DIR") ?>
        </td>
        <td width="80%">
            <input name="DOCUMENTS_DIR"
                   class="adm-detail-content-cell-r"
                   size="40"
                   readonly
                   style="opacity:1;"
                   value="<?= $DOCUMENTS_DIR ?>"/>
            <input id="dir_but"
                   type="button"
                   value="<?= GetMessage("TN_DOCS_OPT_DOCS_DIR_SELECT") ?>"
                   onclick="dirSelector()">
        </td>
    </tr>

    <?= $tabControl->BeginNextTab(); ?>

    <tr>
        <td width="40%" class="adm-detail-content-cell-l">
            <?= GetMessage("TN_DOCS_LICENSE_ENABLE") ?>
        </td>
        <td width="60%">
            <input type="checkbox"
                   <?= (($PROVIDE_LICENSE) ? "checked='checked'" : "") ?>
                   name="PROVIDE_LICENSE"
                   onchange="toggleInputs(!this.checked)"/>
        </td>
    </tr>

    <tr>
        <td> <?= GetMessage("TN_DOCS_LICENSE_USERNAME") ?> </td>
        <td>
            <input name="USERNAME"
                   id="USERNAME"
                   <?= $PROVIDE_LICENSE ? "" : "disabled='disabled'" ?>
                   style="width: 300px;"
                   type="text"
                   value="<?= $USERNAME ?>"/>
        </td>
    </tr>

    <tr>
        <td> <?= GetMessage("TN_DOCS_LICENSE_PASSWORD") ?> </td>
        <td>
            <input name="PASSWORD"
                   id="PASSWORD"
                   <?= $PROVIDE_LICENSE ? "" : "disabled='disabled'" ?>
                   style="width: 300px;"
                   type="password"
                   value="<?= $PASSWORD ?>"/>
        </td>
    </tr>

    <tr>
        <td> <?= GetMessage("TN_DOCS_LICENSE_CLIENT_ID") ?> </td>
        <td>
            <input name="CLIENT_ID"
                   id="CLIENT_ID"
                   <?= $PROVIDE_LICENSE ? "" : "disabled='disabled'" ?>
                   style="width: 300px;"
                   type="text"
                   value="<?= $CLIENT_ID ?>"/>
        </td>
    </tr>

    <tr>
        <td> <?= GetMessage("TN_DOCS_LICENSE_SECRET") ?> </td>
        <td>
            <input name="SECRET"
                   id="SECRET"
                   <?= $PROVIDE_LICENSE ? "" : "disabled='disabled'" ?>
                   style="width: 300px;"
                   type="password"
                   value="<?= $SECRET ?>"/>
        </td>
    </tr>

    <? if ($saleModule): ?>
        <?= $tabControl->BeginNextTab(); ?>

        <?echo BeginNote();?>
        <?echo GetMessage("TN_DOCS_EMAIL_DESCRIPTION")?><br>
        <?echo EndNote();?>

        <tr>
            <td width="20%" class="adm-detail-content-cell-l">
                <?= GetMessage("TN_DOCS_EMAIL_MAIL_EVENT_ID") ?>
            </td>
            <td width="80%">
            <select name="MAIL_EVENT_ID" id="MAIL_EVENT_ID">
                <option value="" disabled hidden <?= $MAIL_EVENT_ID ? "" : "selected" ?>>Выберите событие</option>
                <?
                $events = CEventType::GetList(array("LID" => LANGUAGE_ID), $order="TYPE_ID");
                while ($event = $events->Fetch()) {
                    $eventId = htmlspecialcharsbx($event["ID"]);
                    $eventTypeName = htmlspecialcharsbx($event["EVENT_NAME"]);
                    $eventName = htmlspecialcharsbx($event["NAME"]);
                    $sel = $MAIL_EVENT_ID == $eventTypeName ? " selected" : "";
                    echo "<option value='" . $eventTypeName . "'" . $sel . ">" . $eventId . " - " . $eventName . "</option>";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td> <?= GetMessage("TN_DOCS_EMAIL_SITE_ID") ?> </td>
            <td>
                <select name="MAIL_SITE_ID" id="MAIL_SITE_ID">
                <option value="" disabled hidden <?= $MAIL_SITE_ID ? "" : "selected" ?>>Выберите сайт</option>
                <?
                $sites = CSite::GetList($by="sort", $order="desc", array());
                while ($site = $sites->Fetch()) {
                    $siteId = htmlspecialcharsbx($site["ID"]);
                    $siteName = htmlspecialcharsbx($site["NAME"]);
                    $sel = $MAIL_SITE_ID == $siteId ? " selected" : "";
                    echo "<option value='" . $siteId . "'" . $sel . ">" . $siteId . " - " . $siteName . "</option>";
                }
                ?>
            </td>
        </tr>

    <? endif; ?>

    <?= $tabControl->BeginNextTab(); ?>

    <?
    if ($_POST["purge_logs"]) {
        unlink(TN_DOCS_LOG_FILE);
    }
    if ($_POST["download_logs"]) {
        Docs\Utils::download(TN_DOCS_LOG_FILE, "tn_docs_log_" . date("Y-m-d") . ".txt");
    }
    if (file_exists(TN_DOCS_LOG_FILE)) {
    ?>
        <p><?= GetMessage("TN_DOCS_LOGS_LAST_100") ?></p>
        <pre><? print_r(Docs\Utils::tail(TN_DOCS_LOG_FILE, 100)) ?></pre>
        <input name="download_logs" type="submit" value="<?= GetMessage("TN_DOCS_LOGS_DOWNLOAD") ?>" style="margin-right:5px;"/>
        <input name="purge_logs" type="submit" value="<?= GetMessage("TN_DOCS_LOGS_PURGE") ?>"/>
    <?
    } else {
        echo GetMessage("TN_DOCS_LOGS_NO_LOG_FILE");
    }
    ?>

    <? $tabControl->Buttons(); ?>

    <?php $tabControl->End(); ?>

    <input type="submit" name="Update" value="<?= GetMessage("TN_DOCS_OPT_SAVE") ?>"/>

</form>

<?=
    CAdminFileDialog::ShowScript
    (
        Array(
            "event" => "dirSelector",
            "arResultDest" => array(
                "FORM_NAME" => "trustednetdocs_settings",
                "FORM_ELEMENT_NAME" => "DOCUMENTS_DIR",
            ),
            "arPath" => array(),
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

function toggleInputs (state) {
    document.getElementById("USERNAME").disabled = state;
    document.getElementById("PASSWORD").disabled = state;
    document.getElementById("CLIENT_ID").disabled = state;
    document.getElementById("SECRET").disabled = state;
}

</script>

