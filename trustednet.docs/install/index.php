<?php

IncludeModuleLangFile(__FILE__);

Class trustednet_docs extends CModule
{

    var $MODULE_ID = "trustednet.docs";
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function trustednet_docs()
    {
        $arModuleVersion = array();
        include __DIR__ . "/version.php";
        $this->MODULE_NAME = GetMessage("TN_DOCS_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("TN_DOCS_MODULE_DESCRIPTION");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->PARTNER_NAME = GetMessage("TN_DOCS_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("TN_DOCS_PARTNER_URI");
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION, $step;
        $this->InstallFiles();
        RegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(
            GetMessage("TN_DOCS_INSTALL_TITLE"),
            $DOCUMENT_ROOT."/bitrix/modules/" . $this->MODULE_ID . "/install/step.php"
        );
    }

    function InstallFiles()
    {
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION, $step;
        $this->UninstallFiles();
        UnRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(
            GetMessage("TN_DOCS_UNINSTALL_TITLE"),
            $DOCUMENT_ROOT."/bitrix/modules/" . $this->MODULE_ID . "/install/unstep.php"
        );
    }

    function UnInstallFiles()
    {
    }

}
