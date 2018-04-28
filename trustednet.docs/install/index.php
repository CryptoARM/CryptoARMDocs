<?php

use Bitrix\Main\Config\Option;

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

        $step = intval($step);
        $continue = true;
        if ($_REQUEST["choice"] == GetMessage("TN_DOCS_CANCEL_INSTALL")) {
            $continue = false;
        }
        if ($step < 2 && $continue) {
            $APPLICATION->IncludeAdminFile(
                GetMessage("MOD_INSTALL_TITLE"),
                $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step1.php"
            );
        }
        if ($step == 2 && $continue) {
            $APPLICATION->IncludeAdminFile(
                GetMessage("MOD_INSTALL_TITLE"),
                $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step2.php"
            );
        }
        if ($step == 3 && $continue) {
            $APPLICATION->IncludeAdminFile(
                GetMessage("MOD_INSTALL_TITLE"),
                $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step3.php"
            );
        }
        if ($step == 4 && $continue) {
            if ($_REQUEST["dropDB"] == "Y") {
                $this->UnInstallDB();
            } elseif ($_REQUEST["dropLostDocs"]) {
                $lostDocs = unserialize($_REQUEST["dropLostDocs"]);
                foreach ($lostDocs as $id) {
                    $this->dropDocumentChain($id);
                }
            }
            $this->InstallFiles();
            $this->CreateDocsDir();
            $this->InstallModuleOptions();
            $this->InstallDB();
            RegisterModule($this->MODULE_ID);
        }
        if (!$continue) {
            $APPLICATION->IncludeAdminFile(
                GetMessage("MOD_INSTALL_TITLE"),
                $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step4.php"
            );
        }
    }

    function InstallFiles()
    {
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/components/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/",
            true, true
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin",
            true, false
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/js/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/",
            true, true
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes",
            true, true
        );
        return true;
    }

    function CreateDocsDir()
    {
        $docsDir = $_SERVER["DOCUMENT_ROOT"] . "/docs/";
        if (!file_exists($docsDir)) {
            mkdir($docsDir);
        }
    }

    function InstallModuleOptions()
    {
        if (!Option::get("trustednet.docs", "DOCUMENTS_DIR", "")) {
            Option::set("trustednet.docs", "DOCUMENTS_DIR", "/docs/");
        }
    }

    function InstallDB()
    {
        global $DB;
        $this->createTableDocument();
        $this->createTableDocumentProps();
    }

    function createTableDocument()
    {
        global $DB;
        $sql = "CREATE TABLE IF NOT EXISTS `trn_docs` (
                    `ID` int(11) NOT NULL AUTO_INCREMENT,
                    `NAME` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `DESCRIPTION` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `PATH` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `TYPE` tinyint(1) DEFAULT '0',
                    `STATUS` tinyint(1) DEFAULT '0',
                    `SIGNERS` text COLLATE utf8_unicode_ci,
                    `PARENT_ID` int(11) DEFAULT NULL,
                    `CHILD_ID` int(11) DEFAULT NULL,
                    `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`ID`),
                KEY `fk_trn_docs_trn_docs1_idx` (`PARENT_ID`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->Query($sql);
    }

    function createTableDocumentProps()
    {
        global $DB;
        $sql = "CREATE TABLE IF NOT EXISTS `trn_docs_property` (
                    `ID` int(11) NOT NULL AUTO_INCREMENT,
                    `DOCUMENT_ID` int(11) DEFAULT NULL,
                    `TYPE` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `VALUE` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                PRIMARY KEY (`ID`),
                KEY `fk_trn_docs_property_trn_docs_idx` (`DOCUMENT_ID`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->Query($sql);
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION, $step;

        $step = intval($step);
        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(
                GetMessage("MOD_UNINSTALL_TITLE"),
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/unstep1.php"
            );
        }
        if ($step == 2) {
            $this->UnInstallFiles();
            $savedata = $_REQUEST["savedata"];
            if ($savedata != "Y") {
                $this->UnInstallDB();
            }
            UnRegisterModule($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile(
                GetMessage("MOD_UNINSTALL_TITLE"),
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/unstep2.php"
            );
        }
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/components/trustednet/" . $this->MODULE_ID);
        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/admin/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
        );
        DeleteDirFilesEx("/bitrix/js/" . $this->MODULE_ID);
        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes/.default/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default/"
        );
        DeleteDirFilesEx("/bitrix/themes/.default/icons/" . $this->MODULE_ID);
        return true;
    }

    function UnInstallDB()
    {
        global $DB;
        $this->dropTableDocument();
        $this->dropTableDocumentProps();
    }

    function dropTableDocument()
    {
        global $DB;
        $sql = "DROP TABLE IF EXISTS `trn_docs`";
        $DB->Query($sql);
    }

    function dropTableDocumentProps()
    {
        global $DB;
        $sql = "DROP TABLE IF EXISTS `trn_docs_property`";
        $DB->Query($sql);
    }

    function dropTableDocumentStatus()
    {
        global $DB;
        $sql = "DROP TABLE IF EXISTS `trn_docs_status`";
        $DB->Query($sql);
    }

    function dropDocumentChain($id)
    {
        global $DB;
        // Try to find parent doc
        $sql = 'SELECT `PARENT_ID` FROM `trn_docs` WHERE `ID`=' . $id;
        $res = $DB->Query($sql)->Fetch();
        $parentId = $res["PARENT_ID"];

        $sql = 'DELETE FROM `trn_docs`'
            . 'WHERE ID = ' . $id;
        $DB->Query($sql);
        $sql = 'DELETE FROM `trn_docs_property`'
            . 'WHERE DOCUMENT_ID = ' . $id;
        $DB->Query($sql);

        if ($parentId) {
            $this->dropDocumentChain($parentId);
        }
    }

}
