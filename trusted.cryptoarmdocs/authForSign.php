<?php

use Trusted\CryptoARM\Docs;
use Trusted\Id;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
//checks the name of currently installed core from highest possible version to lowest
$coreIds = array(
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
);
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $coreId . "/";
    if (file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}
Loader::includeModule($module_id);

if (!$_REQUEST["accessToken"]) {
    echo "accessToken does not exist";
    return [
        "success" => false,
        "message" => "accessToken does not exist",
    ];
}

$uuid = $_REQUEST["accessToken"];

if (!ModuleManager::isModuleInstalled("trusted.id")) {
    echo "Not installed trusted.id";
    return [
        "success" => false,
        "message" => "Not installed trusted.id",
    ];
}

Loader::includeModule("trusted.id");

if (!Docs\Utils::isValidUuid($uuid)) {
    echo "accessToken is not valid";
    return [
        "success" => false,
        "message" => "accessToken is not valid",
    ];
}

if (!(TR_ID_OPT_CLIENT_ID && TR_ID_OPT_CLIENT_SECRET)) {
    echo "client id and/or client secret is not find";
    return [
        "success" => false,
        "message" => "client id and/or client secret is not find",
    ];
}

global $USER;

if ($USER->IsAuthorized()) {
    $transactionInfo = Docs\Database::getTransaction($uuid);

    if ($transactionInfo["TRANSACTION_STATUS"] === DOC_TRANSACTION_COMPLETED) {
        echo "transaction is completed";
        return [
            "success" => false,
            "message" => "transaction is completed",
        ];
    }

    if ($transactionInfo["TRANSACTION_TYPE"] === DOC_TRANSACTION_TYPE_VERIFY) {
        echo "wrong transaction type";
        return [
            "success" => false,
            "message" => "wrong transaction type",
        ];
    }
    if ($transactionInfo["USER_ID"] == $USER->GetID()) {
        $url = "cryptoarm://sign/" . TR_CA_DOCS_AJAX_CONTROLLER . "?command=JSON&accessToken=" . $uuid;
        echo "open cryptoarmgost";
        header("Location: " . $url);
        die();
    } else {
        $USER->Logout();
        ?>
        <script>
            let clientId = "<?= TR_ID_OPT_CLIENT_ID  ?>";
            let redirectUri = "<?= TR_ID_URI_HOST ?>" + "/bitrix/components/trusted/id/authorize.php";
            let scope = "userprofile";
            let url = "https://id.trusted.plus/idp/sso/oauth";
            url += "?client_id=" + clientId + "&redirect_uri=" + encodeURIComponent(redirectUri);
            url += "&scope=" + scope + "&state=" + encodeURIComponent(window.location.href) + "&final=true";
            window.location = url;
        </script>
        <?
    }
} else {
    ?>
    <script>
        let clientId = "<?= TR_ID_OPT_CLIENT_ID  ?>";
        let redirectUri = "<?= TR_ID_URI_HOST ?>" + "/bitrix/components/trusted/id/authorize.php";
        let scope = "userprofile";
        let url = "https://id.trusted.plus/idp/sso/oauth";
        url += "?client_id=" + clientId + "&redirect_uri=" + encodeURIComponent(redirectUri);
        url += "&scope=" + scope + "&state=" + encodeURIComponent(window.location.href) + "&final=true";
        window.location = url;
    </script>
    <?
}
