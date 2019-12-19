<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

use Trusted\Id;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;

Loader::includeModule("trusted.id");

define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

function getUserIdByToken($token) {
    if (!IsModuleInstalled("trusted.id")) {
        $answer = [
            "code" => 800,
            "message" => "trusted.id is not installed",
            "data" => []
        ];
        return $answer;
    }

    if (!$token) {
        $answer = [
            "code" => 801,
            "message" => "token is not find",
            "data" => []
        ];
        return $answer;
    }

    try {
        $responseByToken = Id\TAuthCommand::getUserProfileByToken($token);
    } catch (Exception $exception) {
        $answer = [
            "code" => 804,
            "message" => "something wrong",
            "data" => []
        ];
        return $answer;
    }

    if (!$responseByToken["entityId"]) {
        $answer = [
            "code" => 802,
            "message" => "user did not give permission",
            "data" => []
        ];
        return $answer;
    }

    $userInfo = Id\TDataBaseUser::getUserById($responseByToken["entityId"]);

    if (is_null($userInfo) || is_null($userInfo->getUserId())) {
        $answer = [
            "code" => 803,
            "message" => "user is not find",
            "data" => []
        ];
        return $answer;
    }

    $userId = (int)$userInfo->getUserId();

    return $userId;
}
