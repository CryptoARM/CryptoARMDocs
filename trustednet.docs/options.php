<?php
use TrustedNet\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO\File;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

include __DIR__ . "/config.php";
// Include only necessary utility functions from module
include __DIR__ . "/classes/Utils.php";

$module_id = TN_DOCS_MODULE_ID;

$saleModule = ModuleManager::isModuleInstalled("sale");
if ($saleModule) {
    Loader::includeModule("sale");
}

Loc::loadMessages(__FILE__);

$aTabs = array();
$aTabs[] = array(
    "DIV" => "tn_docs_options",
    "TAB" => Loc::getMessage("TN_DOCS_OPT_TAB"),
    "TITLE" => Loc::getMessage("TN_DOCS_OPT_TAB_TITLE")
);
$aTabs[] = array(
    "DIV" => "tn_docs_license",
    "TAB" => Loc::getMessage("TN_DOCS_LICENSE_TAB"),
    "TITLE" => Loc::getMessage("TN_DOCS_LICENSE_TAB_TITLE")
);
if($saleModule) {
    $aTabs[] = array(
        "DIV" => "tn_docs_order",
        "TAB" => Loc::getMessage("TN_DOCS_ORDER_TAB"),
        "TITLE" => Loc::getMessage("TN_DOCS_ORDER_TAB_TITLE")
    );
}
$aTabs[] = array(
    "DIV" => "tn_docs_logs",
    "TAB" => Loc::getMessage("TN_DOCS_LOGS_TAB"),
    "TITLE" => Loc::getMessage("TN_DOCS_LOGS_TAB_TITLE")
);

$tabControl = new CAdminTabControl("trustedTabControl", $aTabs, true, true);

// TODO: move to Utils
function CheckDocumentsDir($dir) {
    $docRoot = $_SERVER["DOCUMENT_ROOT"];
    $fullPath = $docRoot . $dir;
    // Expand extra /../
    $fullPath = realpath($fullPath);

    // Check if we are in bitrix root
    $len = strlen($docRoot);
    if (strncmp($fullPath, $docRoot, $len) < 0 || strcmp($fullPath, $docRoot) == 0) {
        return Loc::getMessage("TN_DOCS_DOCS_DIR_CANNOT_USE_SYSTEM_DIRECTORY");
    }

    // Check for entering bitrix system directory
    if (preg_match("/^bitrix($|\/*)/", $dir)) {
        return Loc::getMessage("TN_DOCS_DOCS_DIR_CANNOT_USE_SYSTEM_DIRECTORY");
    }

    // Check for permissions
    if (!is_readable($fullPath) && !is_writable($fullPath)) {
        return Loc::getMessage("TN_DOCS_DOCS_DIR_NO_ACCESS_TO_DIRECTORY");
    }

    return true;
}

$moduleOptions = array(
    "DOCUMENTS_DIR",
    "PROVIDE_LICENSE", "TN_USERNAME", "TN_PASSWORD", "TN_CLIENT_ID", "TN_CLIENT_SECRET",
    "EVENT_SIGNED_BY_CLIENT", "EVENT_SIGNED_BY_SELLER", "EVENT_SIGNED_BY_BOTH",
    "EVENT_SIGNED_BY_CLIENT_ALL_DOCS", "EVENT_SIGNED_BY_SELLER_ALL_DOCS", "EVENT_SIGNED_BY_BOTH_ALL_DOCS",
    "EVENT_EMAIL_SENT", "EVENT_EMAIL_READ",
    "MAIL_EVENT_ID", "MAIL_TEMPLATE_ID",
);

function UpdateOption($option, $value = false) {
    // Try to use value from POST if no explicit value is provided
    if ($value === false) {
        if (isset($_POST[$option])) {
            $$option = (string)$_POST[$option];
        } else {
            $$option = "";
        }
    } else {
        $$option = (string)$value;
    }
    Option::set(TN_DOCS_MODULE_ID, $option, $$option);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid()) {
    if (isset($_POST["Update"])) {
        $docsDirCheck = CheckDocumentsDir($_POST["DOCUMENTS_DIR"]);
        if ($docsDirCheck === true) {
            UpdateOption("DOCUMENTS_DIR");
        } else {
            CAdminMessage::ShowMessage($docsDirCheck);
        }
        UpdateOption("PROVIDE_LICENSE");
        if (isset($_POST["PROVIDE_LICENSE"])) {
            if (!$_POST["TN_USERNAME"] ||
                !$_POST["TN_PASSWORD"] ||
                !$_POST["TN_CLIENT_ID"] ||
                !$_POST["TN_CLIENT_SECRET"]) {
                CAdminMessage::ShowMessage(Loc::getMessage("TN_DOCS_LICENSE_NO_EMPTY_FIELDS"));
            } else {
                UpdateOption("TN_USERNAME");
                UpdateOption("TN_PASSWORD");
                UpdateOption("TN_CLIENT_ID");
                UpdateOption("TN_CLIENT_SECRET");
            }
        }
        UpdateOption("EVENT_SIGNED_BY_CLIENT");
        UpdateOption("EVENT_SIGNED_BY_SELLER");
        UpdateOption("EVENT_SIGNED_BY_BOTH");
        UpdateOption("EVENT_SIGNED_BY_CLIENT_ALL_DOCS");
        UpdateOption("EVENT_SIGNED_BY_SELLER_ALL_DOCS");
        UpdateOption("EVENT_SIGNED_BY_BOTH_ALL_DOCS");
        UpdateOption("EVENT_EMAIL_SENT");
        UpdateOption("EVENT_EMAIL_READ");
        UpdateOption("MAIL_EVENT_ID");
        UpdateOption("MAIL_TEMPLATE_ID");
    }
}

foreach ($moduleOptions as $option) {
    $$option = Option::get(TN_DOCS_MODULE_ID, $option, "");
}

$tabControl->Begin();

?>

<form method="POST" enctype="multipart/form-data"
      action="<?= $APPLICATION->GetCurPage() ?>?lang=<?= LANGUAGE_ID ?>&mid=<?= TN_DOCS_MODULE_ID ?>"
      name="trustednetdocs_settings">

    <?= bitrix_sessid_post(); ?>

    <?= $tabControl->BeginNextTab(); ?>

    <tr>
        <td width="20%">
            <?= Loc::getMessage("TN_DOCS_DOCS_DIR") ?>
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
                   value="<?= Loc::getMessage("TN_DOCS_DOCS_DIR_SELECT") ?>"
                   onclick="dirSelector()">
        </td>
    </tr>

    <?= $tabControl->BeginNextTab(); ?>

    <tr>
        <td width="40%">
            <?= Loc::getMessage("TN_DOCS_LICENSE_ENABLE") ?>
        </td>
        <td width="60%">
            <input type="checkbox"
                   <?= (($PROVIDE_LICENSE) ? "checked='checked'" : "") ?>
                   name="PROVIDE_LICENSE"
                   value="true"
                   onchange="toggleInputs(!this.checked)"/>
        </td>
    </tr>

    <tr>
        <td> <?= Loc::getMessage("TN_DOCS_LICENSE_TN_USERNAME") ?> </td>
        <td>
            <input name="TN_USERNAME"
                   id="TN_USERNAME"
                   <?= $PROVIDE_LICENSE ? "" : "disabled='disabled'" ?>
                   style="width: 300px;"
                   type="text"
                   value="<?= $TN_USERNAME ?>"/>
        </td>
    </tr>

    <tr>
        <td> <?= Loc::getMessage("TN_DOCS_LICENSE_TN_PASSWORD") ?> </td>
        <td>
            <input name="TN_PASSWORD"
                   id="TN_PASSWORD"
                   <?= $PROVIDE_LICENSE ? "" : "disabled='disabled'" ?>
                   style="width: 300px;"
                   type="password"
                   value="<?= $TN_PASSWORD ?>"/>
        </td>
    </tr>

    <tr>
        <td> <?= Loc::getMessage("TN_DOCS_LICENSE_TN_CLIENT_ID") ?> </td>
        <td>
            <input name="TN_CLIENT_ID"
                   id="TN_CLIENT_ID"
                   <?= $PROVIDE_LICENSE ? "" : "disabled='disabled'" ?>
                   style="width: 300px;"
                   type="text"
                   value="<?= $TN_CLIENT_ID ?>"/>
        </td>
    </tr>

    <tr>
        <td> <?= Loc::getMessage("TN_DOCS_LICENSE_TN_CLIENT_SECRET") ?> </td>
        <td>
            <input name="TN_CLIENT_SECRET"
                   id="TN_CLIENT_SECRET"
                   <?= $PROVIDE_LICENSE ? "" : "disabled='disabled'" ?>
                   style="width: 300px;"
                   type="password"
                   value="<?= $TN_CLIENT_SECRET ?>"/>
        </td>
    </tr>

    <? if ($saleModule): ?>
        <?= $tabControl->BeginNextTab(); ?>

        <tr class="heading">
            <td colspan="2"><?= Loc::getMessage("TN_DOCS_EVENTS_HEADING") ?></td>
        </tr>

        <tr>
            <td colspan="2">
                <?
                echo BeginNote(), Loc::getMessage("TN_DOCS_EVENTS_DESCRIPTION"), EndNote();
                ?>
            </td>
        </tr>

        <?
        $dbResultList = CSaleStatus::GetList(
            array("SORT" => "ASC"),
            array("LID" => "ru"),
            false,
            false,
            array("ID", "NAME")
        );
        $orderStatuses = array();
        while ($status = $dbResultList->Fetch()) {
            $orderStatuses[] = array(
                "ID" => $status["ID"],
                "NAME" => $status["NAME"],
            );
        }
        ?>

        <tr>
            <td width="30%" class="trustednetdocs_opt_multiline_cell"> <?= Loc::getMessage("TN_DOCS_EVENTS_SIGNED_BY_CLIENT") ?> </td>
            <td width="70%">
                <select name="EVENT_SIGNED_BY_CLIENT" id="EVENT_SIGNED_BY_CLIENT">
                    <option value="" <?= $EVENT_SIGNED_BY_CLIENT ? "" : "selected" ?>><?= Loc::getMessage("TN_DOCS_EVENTS_DO_NOTHING") ?></option>
                    <?
                    foreach ($orderStatuses as $status) {
                        $statusId = htmlspecialcharsbx($status["ID"]);
                        $statusName = htmlspecialcharsbx($status["NAME"]);
                        $sel = $EVENT_SIGNED_BY_CLIENT == $statusId ? " selected" : "";
                        echo "<option value='" . $statusId . "'" . $sel . ">" . $statusId . " - " . $statusName . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="checkbox"
                       <?= (($EVENT_SIGNED_BY_CLIENT_ALL_DOCS) ? "checked='checked'" : "") ?>
                       name="EVENT_SIGNED_BY_CLIENT_ALL_DOCS"
                       value="true"/>
                <?= Loc::getMessage("TN_DOCS_EVENTS_SIGNED_WAIT_ALL_DOCS") ?>
            </td>
        </tr>

        <tr>
            <td> <?= Loc::getMessage("TN_DOCS_EVENTS_SIGNED_BY_SELLER") ?> </td>
            <td>
                <select name="EVENT_SIGNED_BY_SELLER" id="EVENT_SIGNED_BY_SELLER">
                    <option value="" <?= $EVENT_SIGNED_BY_SELLER ? "" : "selected" ?>><?= Loc::getMessage("TN_DOCS_EVENTS_DO_NOTHING") ?></option>
                    <?
                    foreach ($orderStatuses as $status) {
                        $statusId = htmlspecialcharsbx($status["ID"]);
                        $statusName = htmlspecialcharsbx($status["NAME"]);
                        $sel = $EVENT_SIGNED_BY_SELLER == $statusId ? " selected" : "";
                        echo "<option value='" . $statusId . "'" . $sel . ">" . $statusId . " - " . $statusName . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="checkbox"
                       <?= (($EVENT_SIGNED_BY_SELLER_ALL_DOCS) ? "checked='checked'" : "") ?>
                       name="EVENT_SIGNED_BY_SELLER_ALL_DOCS"
                       value="true"/>
                <?= Loc::getMessage("TN_DOCS_EVENTS_SIGNED_WAIT_ALL_DOCS") ?>
            </td>
        </tr>

        <tr>
            <td> <?= Loc::getMessage("TN_DOCS_EVENTS_SIGNED_BY_BOTH") ?> </td>
            <td>
                <select name="EVENT_SIGNED_BY_BOTH" id="EVENT_SIGNED_BY_BOTH">
                    <option value="" <?= $EVENT_SIGNED_BY_BOTH ? "" : "selected" ?>><?= Loc::getMessage("TN_DOCS_EVENTS_DO_NOTHING") ?></option>
                    <?
                    foreach ($orderStatuses as $status) {
                        $statusId = htmlspecialcharsbx($status["ID"]);
                        $statusName = htmlspecialcharsbx($status["NAME"]);
                        $sel = $EVENT_SIGNED_BY_BOTH == $statusId ? " selected" : "";
                        echo "<option value='" . $statusId . "'" . $sel . ">" . $statusId . " - " . $statusName . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="checkbox"
                       <?= (($EVENT_SIGNED_BY_BOTH_ALL_DOCS) ? "checked='checked'" : "") ?>
                       name="EVENT_SIGNED_BY_BOTH_ALL_DOCS"
                       value="true"/>
                <?= Loc::getMessage("TN_DOCS_EVENTS_SIGNED_WAIT_ALL_DOCS") ?>
            </td>
        </tr>

        <tr>
            <td> <?= Loc::getMessage("TN_DOCS_EVENTS_EMAIL_SENT") ?> </td>
            <td>
                <select name="EVENT_EMAIL_SENT" id="EVENT_EMAIL_SENT">
                    <option value="" <?= $EVENT_EMAIL_SENT ? "" : "selected" ?>><?= Loc::getMessage("TN_DOCS_EVENTS_DO_NOTHING") ?></option>
                    <?
                    foreach ($orderStatuses as $status) {
                        $statusId = htmlspecialcharsbx($status["ID"]);
                        $statusName = htmlspecialcharsbx($status["NAME"]);
                        $sel = $EVENT_EMAIL_SENT == $statusId ? " selected" : "";
                        echo "<option value='" . $statusId . "'" . $sel . ">" . $statusId . " - " . $statusName . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <td> <?= Loc::getMessage("TN_DOCS_EVENTS_EMAIL_READ") ?> </td>
            <td>
                <select name="EVENT_EMAIL_READ" id="EVENT_EMAIL_READ">
                    <option value="" <?= $EVENT_EMAIL_READ ? "" : "selected" ?>><?= Loc::getMessage("TN_DOCS_EVENTS_DO_NOTHING") ?></option>
                    <?
                    foreach ($orderStatuses as $status) {
                        $statusId = htmlspecialcharsbx($status["ID"]);
                        $statusName = htmlspecialcharsbx($status["NAME"]);
                        $sel = $EVENT_EMAIL_READ == $statusId ? " selected" : "";
                        echo "<option value='" . $statusId . "'" . $sel . ">" . $statusId . " - " . $statusName . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr class="heading">
            <td colspan="2"><?= Loc::getMessage("TN_DOCS_EMAIL_HEADING") ?></td>
        </tr>

        <tr>
            <td colspan="2">
                <?
                echo BeginNote(), Loc::getMessage("TN_DOCS_EMAIL_DESCRIPTION"), EndNote();
                ?>
            </td>
        </tr>

        <tr>
            <td>
                <?= Loc::getMessage("TN_DOCS_EMAIL_MAIL_EVENT_ID") ?>
            </td>
            <td>
                <select name="MAIL_EVENT_ID" id="MAIL_EVENT_ID">
                    <option value="" <?= $MAIL_EVENT_ID ? "" : "selected" ?>><?= Loc::getMessage("TN_DOCS_EMAIL_NOT_SELECTED") ?></option>
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
                </select>
            </td>
        </tr>

        <tr>
            <td> <?= Loc::getMessage("TN_DOCS_EMAIL_TEMPLATE_ID") ?> </td>
            <td>
                <select name="MAIL_TEMPLATE_ID" id="MAIL_TEMPLATE_ID">
                    <option value="" <?= $MAIL_TEMPLATE_ID ? "" : "selected" ?>><?= Loc::getMessage("TN_DOCS_EMAIL_NOT_SELECTED") ?></option>
                    <?
                    $templates = CEventMessage::GetList($by = "id", $order = "asc", array("TYPE_ID" => $MAIL_EVENT_ID));
                    while ($template = $templates->Fetch()) {
                        $templateId = htmlspecialcharsbx($template["ID"]);
                        $templateSubject = htmlspecialcharsbx($template["SUBJECT"]);
                        $sel = $MAIL_TEMPLATE_ID == $templateId ? " selected" : "";
                        echo "<option value='" . $templateId . "'" . $sel . ">" . $templateId . " - " . $templateSubject . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>

    <? endif; ?>

    <?= $tabControl->BeginNextTab(); ?>

    <?
    if ($_POST["purge_logs"]) {
        File::deleteFile(TN_DOCS_LOG_FILE);
    }
    if ($_POST["download_logs"]) {
        Docs\Utils::download(TN_DOCS_LOG_FILE, "tn_docs_log_" . date("Y-m-d") . ".txt");
    }
    if (file_exists(TN_DOCS_LOG_FILE)) {
    ?>
        <p><?= Loc::getMessage("TN_DOCS_LOGS_LAST_100") ?></p>
        <pre><? print_r(Docs\Utils::tail(TN_DOCS_LOG_FILE, 100)) ?></pre>
        <input name="download_logs" type="submit" value="<?= Loc::getMessage("TN_DOCS_LOGS_DOWNLOAD") ?>" style="margin-right:5px;"/>
        <input name="purge_logs" type="submit" value="<?= Loc::getMessage("TN_DOCS_LOGS_PURGE") ?>"/>
    <?
    } else {
        echo Loc::getMessage("TN_DOCS_LOGS_NO_LOG_FILE");
    }
    ?>

    <? $tabControl->Buttons(); ?>

    <?php $tabControl->End(); ?>

    <input type="submit" name="Update" value="<?= Loc::getMessage("TN_DOCS_OPT_SAVE") ?>"/>

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
    document.getElementById("TN_USERNAME").disabled = state;
    document.getElementById("TN_PASSWORD").disabled = state;
    document.getElementById("TN_CLIENT_ID").disabled = state;
    document.getElementById("TN_CLIENT_SECRET").disabled = state;
}

</script>

