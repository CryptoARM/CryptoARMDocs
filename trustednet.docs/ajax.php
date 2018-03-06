<?php

define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

header('Content-Type: application/json; charset=' . LANG_CHARSET);

// AJAX Controller

$command = $_GET['command'];
if (isset($command)) {
    $params = $_POST;
    switch ($command) {
        case "upload":
            $res = AjaxCommand::upload($params);
            break;
        case "updateStatus":
            $res = AjaxCommand::updateStatus($params);
            break;
        case "block":
            $res = AjaxCommand::block($params);
            break;
        case "unblock":
            $res = AjaxCommand::unblock($params);
            break;
        case "remove":
            $res = AjaxCommand::remove($params);
            break;
        case "view":
            $res = AjaxCommand::view($params);
            break;
        case "download":
            $res = AjaxCommand::download($params);
            break;
        case "content":
            $res = AjaxCommand::content($_GET);
            return $res;
            break;
        case "token":
            $res = AjaxCommand::token($_GET);
            break;
        default:
            $res = array("success" => false, "message" => "Unknown command '" . $command . "'");
    }
}
echo json_encode($res);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");

