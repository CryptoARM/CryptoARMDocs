<?php
use TrustedNet\Docs;

include __DIR__ . "/config.php";
$module_id = TN_DOCS_MODULE_ID;
CModule::IncludeModule($module_id);

IncludeModuleLangFile(__FILE__);

$aTabs = array(
    array(
        "DIV" => "tn_docs_options",
        "TAB" => GetMessage("TN_DOCS_OPT_TAB"),
        "TITLE" => GetMessage("TN_DOCS_OPT_TAB_TITLE")
    ),
    array(
        "DIV" => "tn_docs_license",
        "TAB" => GetMessage("TN_DOCS_LICENSE_TAB"),
        "TITLE" => GetMessage("TN_DOCS_LICENSE_TAB_TITLE")
    ),
    array(
        "DIV" => "tn_docs_logs",
        "TAB" => GetMessage("TN_DOCS_LOGS_TAB"),
        "TITLE" => GetMessage("TN_DOCS_LOGS_TAB_TITLE")
    ),
);

$tabControl = new CAdminTabControl("trustedTabControl", $aTabs, true, true);

$DOCUMENTS_DIR = COption::GetOptionString($module_id, 'DOCUMENTS_DIR', "docs");

$PROVIDE_LICENSE = COption::GetOptionString($module_id, "PROVIDE_LICENSE", "");
$USERNAME = COption::GetOptionString($module_id, "USERNAME", "");
$PASSWORD = COption::GetOptionString($module_id, "PASSWORD", "");
$CLIENT_ID = COption::GetOptionString($module_id, "CLIENT_ID", "");
$SECRET = COption::GetOptionString($module_id, "SECRET", "");

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
    if (isset($_POST["Update"])) {

        if (isset($_POST["DOCUMENTS_DIR"])) {
            $documentsDirFromPost = (string)$_POST["DOCUMENTS_DIR"];
        }
        $documentsDirFromPost = TrimDocumentsDir($documentsDirFromPost);
        $checkRes = CheckDocumentsDir($documentsDirFromPost);
        if ($checkRes === true) {
            $DOCUMENTS_DIR = $documentsDirFromPost;
            COption::SetOptionString($module_id, "DOCUMENTS_DIR", $DOCUMENTS_DIR);
        } else {
            CAdminMessage::ShowMessage($checkRes);
        }

        if (isset($_POST["PROVIDE_LICENSE"])) {
            $PROVIDE_LICENSE = (string)$_POST["PROVIDE_LICENSE"];
            COption::SetOptionString($module_id, "PROVIDE_LICENSE", "on");
        } else {
            $PROVIDE_LICENSE = false;
            COption::SetOptionString($module_id, "PROVIDE_LICENSE", "");
        }

        if (isset($_POST["USERNAME"])) {
            $USERNAME = (string)$_POST["USERNAME"];
            COption::SetOptionString($module_id, "USERNAME", $USERNAME);
        }

        if (isset($_POST["PASSWORD"])) {
            $PASSWORD = (string)$_POST["PASSWORD"];
            COption::SetOptionString($module_id, "PASSWORD", $PASSWORD);
        }

        if (isset($_POST["CLIENT_ID"])) {
            $CLIENT_ID = (string)$_POST["CLIENT_ID"];
            COption::SetOptionString($module_id, "CLIENT_ID", $CLIENT_ID);
        }

        if (isset($_POST["SECRET"])) {
            $SECRET = (string)$_POST["SECRET"];
            COption::SetOptionString($module_id, "SECRET", $SECRET);
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
                   value="<?= $DOCUMENTS_DIR ?>"/>
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
        <input name="download_logs" type="submit" value="<?= GetMessage("TN_DOCS_LOGS_DOWNLOAD") ?>"/>
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

<script>
    function toggleInputs (state) {
        document.getElementById("USERNAME").disabled = state;
        document.getElementById("PASSWORD").disabled = state;
        document.getElementById("CLIENT_ID").disabled = state;
        document.getElementById("SECRET").disabled = state;
    }
</script>

