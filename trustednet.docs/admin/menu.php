<?php

if ($APPLICATION->GetGroupRight("trustednet.docs") >= "R") {

    if (\Bitrix\Main\ModuleManager::isModuleInstalled('trustednet.docs')) {
        IncludeModuleLangFile(__FILE__);
        $aMenu = array(
            "parent_menu" => "global_menu_services",
            "section" => "trustednet_docs",
            "sort" => 20,
            "text" => GetMessage("TN_DOCS_MENU_SECTION"),
            "title" => GetMessage("TN_DOCS_MENU_SECTION"),
            "icon" => "trustednetdocs_menu_icon",
            "page_icon" => "trustednetdocs_page_icon",
            "items_id" => "menu_trustednet.docs",
            "items" => array()
        );


        $Menu[] = array("text" => GetMessage("TN_DOCS_MENU_DOCUMENTS"),
            "url" => "trustednet_documents.php?lang=" . LANGUAGE_ID,
            // more_url assigns page to the menu entry
            "more_url" => array("trustednet_documents_upload.php"),
            "title" => GetMessage("TN_DOCS_MENU_DOCUMENTS")
        );
        if (CModule::IncludeModule("sale"))
            $Menu[] = array("text" => GetMessage("TN_DOCS_MENU_DOCUMENTS_BY_ORDER"),
                "url" => "trustednet_documents_by_order.php?lang=" . LANGUAGE_ID,
                "more_url" => array(),
                "title" => GetMessage("TN_DOCS_MENU_DOCUMENTS_BY_ORDER")
            );


        $aMenu["items"] = $Menu;
        return $aMenu;
    }
}
return false;

