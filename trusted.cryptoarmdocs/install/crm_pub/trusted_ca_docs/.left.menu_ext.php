<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/trusted.cryptoarmdocs/install/crm_pub/trusted_ca_docs/.left.menu_ext.php");

$aMenuLinks = array(
    array(
        GetMessage("TR_CA_DOCS_CRM_MENU_USER"),
        SITE_DIR."trusted_ca_docs/",
        array(),
        array(
            "menu_item_id" => "menu_user",
            "counter_id" => "user",
        ),
        "IsModuleInstalled('trusted.cryptoarmdocs')",
    ),
    array(
        GetMessage("TR_CA_DOCS_CRM_MENU_WF"),
        SITE_DIR."trusted_ca_docs/wf/",
        array(),
        array(
            "menu_item_id" => "menu_wf",
            "counter_id" => "wf",
        ),
        "IsModuleInstalled('trusted.cryptoarmdocs')",
    )
);

