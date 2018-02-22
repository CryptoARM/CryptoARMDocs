<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('trustednet.docs');

include_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trustednet.docs/ajax.php';

