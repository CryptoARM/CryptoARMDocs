<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight("trusted.cryptoarmdocs") >= "R") {

    if (ModuleManager::isModuleInstalled('trusted.cryptoarmdocs')) {
        $aMenu = array(
            "parent_menu" => "global_menu_services",
            "section" => "trusted_cryptoarm_docs",
            "sort" => 20,
            "text" => Loc::getMessage("TR_CA_DOCS_MENU_SECTION"),
            "title" => Loc::getMessage("TR_CA_DOCS_MENU_SECTION"),
            "icon" => "trca-menu-icon",
            "page_icon" => "trustedcryptoarmdocs_page_icon",
            "items_id" => "menu_trusted.cryptoarmdocs",
            "items" => array()
        );

        $menuItems = array();

        $menuItems[] = array("text" => Loc::getMessage("TR_CA_DOCS_MENU_DOCUMENTS"),
            "url" => "trusted_cryptoarm_docs.php",
            // more_url assigns page to the menu entry
            "more_url" => array("trusted_cryptoarm_docs_upload.php"),
            "title" => Loc::getMessage("TR_CA_DOCS_MENU_DOCUMENTS")
        );

        $menuItems[] = array("text" => Loc::getMessage("TR_CA_DOCS_MENU_DOCUMENTS_BY_USER"),
            "url" => "trusted_cryptoarm_docs_by_user.php",
            "more_url" => array("trusted_cryptoarm_docs_upload_by_user.php"),
            "title" => Loc::getMessage("TR_CA_DOCS_MENU_DOCUMENTS_BY_USER")
        );

        if (isModuleInstalled('trusted.cryptoarmdocsorders')) {
            if (Loader::includeModule("sale")) {
                $menuItems[] = array("text" => Loc::getMessage("TR_CA_DOCS_MENU_DOCUMENTS_BY_ORDER"),
                    "url" => "trusted_cryptoarm_docs_by_order.php",
                    "more_url" => array("trusted_cryptoarm_docs_upload_by_order.php"),
                    "title" => Loc::getMessage("TR_CA_DOCS_MENU_DOCUMENTS_BY_ORDER")
                );
            }
        }

        if (isModuleInstalled('trusted.cryptoarmdocsforms')) {
            $menuItems[] = array("text" => Loc::getMessage("TR_CA_DOCS_MENU_DOCUMENTS_BY_FORM"),
                "url" => "trusted_cryptoarm_docs_by_form.php",
                "title" => Loc::getMessage("TR_CA_DOCS_MENU_DOCUMENTS_BY_FORM")
            );
        }

        $aMenu["items"] = $menuItems;
        return $aMenu;
    }
}
return false;

