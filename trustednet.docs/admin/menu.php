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

        $menuItems = array();

        $menuItems[] = array("text" => GetMessage("TN_DOCS_MENU_DOCUMENTS"),
            "url" => "trustednet_documents.php?lang=" . LANGUAGE_ID,
            // more_url assigns page to the menu entry
            "more_url" => array("trustednet_documents_upload.php"),
            "title" => GetMessage("TN_DOCS_MENU_DOCUMENTS")
        );

        $menuItems[] = array("text" => GetMessage("TN_DOCS_MENU_DOCUMENTS_BY_USER"),
            "url" => "trustednet_documents_by_user.php?lang=" . LANGUAGE_ID,
            "more_url" => array("trustednet_documents_upload_by_user.php"),
            "title" => GetMessage("TN_DOCS_MENU_DOCUMENTS_BY_USER")
        );

        if (CModule::IncludeModule("sale"))
            $menuItems[] = array("text" => GetMessage("TN_DOCS_MENU_DOCUMENTS_BY_ORDER"),
                "url" => "trustednet_documents_by_order.php?lang=" . LANGUAGE_ID,
                "more_url" => array("trustednet_documents_upload_by_order.php"),
                "title" => GetMessage("TN_DOCS_MENU_DOCUMENTS_BY_ORDER")
            );


        $aMenu["items"] = $menuItems;
        return $aMenu;
    }
}
return false;

