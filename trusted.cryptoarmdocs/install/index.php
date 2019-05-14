<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

Class trusted_cryptoarmdocs extends CModule
{
    // Required by the marketplace standards
    var $MODULE_ID = "trusted.cryptoarmdocs";
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function trusted_cryptoarmdocs()
    {
        self::__construct();
    }

    function __construct()
    {
        $arModuleVersion = array();
        include __DIR__ . "/version.php";
        $this->MODULE_NAME = Loc::getMessage("TR_CA_DOCS_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TR_CA_DOCS_MODULE_DESCRIPTION");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->PARTNER_NAME = GetMessage("TR_CA_DOCS_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("TR_CA_DOCS_PARTNER_URI");
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        $step = (int)$request["step"];

        if (!$this->d7Support()) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("MOD_INSTALL_TITLE"),
                $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step_no_d7.php"
            );
        }

        $continue = true;
        if ($request["choice"] == Loc::getMessage("TR_CA_DOCS_CANCEL_INSTALL")) {
            $continue = false;
        }
        if ($step < 2 && $continue) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("MOD_INSTALL_TITLE"),
                $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step1.php"
            );
        }
        if ($step == 2 && $continue) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("MOD_INSTALL_TITLE"),
                $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step2.php"
            );
        }
        if ($step == 3 && $continue) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("MOD_INSTALL_TITLE"),
                $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step3.php"
            );
        }
        if ($step == 4 && $continue) {
            if ($request["dropDB"] == "Y") {
                $this->UnInstallDB();
            } elseif ($request["dropLostDocs"]) {
                $lostDocs = unserialize($request["dropLostDocs"]);
                foreach ($lostDocs as $id) {
                    $this->dropDocumentChain($id);
                }
            }
            $this->InstallFiles();
            $this->CreateDocsDir();
            $this->InstallModuleOptions();
            $this->InstallDB();
            $this->InstallMailEvent();
            ModuleManager::registerModule($this->MODULE_ID);
        }
        if (!$continue) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("MOD_INSTALL_TITLE"),
                $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step_cancel.php"
            );
        }
    }

    function d7Support()
    {
        return CheckVersion(ModuleManager::getVersion("main"), "14.00.00");
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
        if (!Option::get("trusted.cryptoarmdocs", "DOCUMENTS_DIR", "")) {
            Option::set("trusted.cryptoarmdocs", "DOCUMENTS_DIR", "/docs/");
        }
        if (!Option::get("trusted.cryptoarmdocs", "MAIL_EVENT_ID", "")) {
            Option::set("trusted.cryptoarmdocs", "MAIL_EVENT_ID", "TR_CA_DOCS_MAIL_BY_ORDER");
        }
        if (!Option::get("trusted.cryptoarmdocs", "MAIL_EVENT_ID_TO", "")) {
            Option::set("trusted.cryptoarmdocs", "MAIL_EVENT_ID_TO", "TR_CA_DOCS_MAIL_TO");
        }
        if (!Option::get("trusted.cryptoarmdocs", "MAIL_EVENT_ID_SHARE", "")) {
            Option::set("trusted.cryptoarmdocs", "MAIL_EVENT_ID_SHARE", "TR_CA_DOCS_MAIL_SHARE");
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
        $sql = "CREATE TABLE IF NOT EXISTS `tr_ca_docs` (
                    `ID` int(11) NOT NULL AUTO_INCREMENT,
                    `NAME` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `DESCRIPTION` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `PATH` text COLLATE utf8_unicode_ci DEFAULT NULL,
                    `TYPE` tinyint(1) DEFAULT '0',
                    `STATUS` tinyint(1) DEFAULT '0',
                    `PARENT_ID` int(11) DEFAULT NULL,
                    `CHILD_ID` int(11) DEFAULT NULL,
                    `HASH` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `SIGNATURES` text COLLATE utf8_unicode_ci,
                    `SIGNERS` text COLLATE utf8_unicode_ci,
                    `BLOCK_BY` int(11) DEFAULT NULL,
                    `BLOCK_TOKEN` varchar(36) DEFAULT NULL,
                    `BLOCK_TIME` timestamp DEFAULT '0',
                    `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`ID`),
                KEY `fk_tr_ca_docs_tr_ca_docs1_idx` (`PARENT_ID`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->Query($sql);
    }

    function createTableDocumentProps()
    {
        global $DB;
        $sql = "CREATE TABLE IF NOT EXISTS `tr_ca_docs_property` (
                    `ID` int(11) NOT NULL AUTO_INCREMENT,
                    `DOCUMENT_ID` int(11) DEFAULT NULL,
                    `TYPE` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `VALUE` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                PRIMARY KEY (`ID`),
                KEY `fk_tr_ca_docs_property_tr_ca_docs_idx` (`DOCUMENT_ID`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->Query($sql);
    }

    function InstallMailEvent()
    {
        $this->createMailEventByOrder();
        $this->createMailEventTo();
        $this->createMailEventShare();
    }

    function createMailEventByOrder() {
        $obEventType = new CEventType;
        $obEventType->add(
            array(
                "LID" => "ru",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_BY_ORDER",
                "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_NAME_RU"),
                "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_DESCRIPTION_RU"),
            )
        );
        $obEventType->add(
            array(
                "LID" => "en",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_BY_ORDER",
                "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_NAME_EN"),
                "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_DESCRIPTION_EN"),
            )
        );
        $obEventMessage = new CEventMessage;
        $sites = CSite::GetList($by = "sort", $order = "asc", array("ACTIVE" => "Y"));
        $siteIds = array();
        while ($site = $sites->Fetch()) {
            $siteIds[] = $site["ID"];
        }
        $templateId = $obEventMessage->add(array(
            "ACTIVE" => "Y",
            "EVENT_NAME" => "TR_CA_DOCS_MAIL_BY_ORDER",
            "LID" => $siteIds,
            "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
            "EMAIL_TO" => "#EMAIL#",
            "SUBJECT" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_SUBJECT"),
            "BODY_TYPE" => "html",
            "MESSAGE" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_BODY"),
        ));
        Option::set("trusted.cryptoarmdocs", "MAIL_TEMPLATE_ID", $templateId);
    }

    function createMailEventTo() {
        $obEventType = new CEventType;
        $obEventType->add(
            array(
                "LID" => "ru",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_TO",
                "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_TO_NAME_RU"),
                "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_TO_DESCRIPTION_RU"),
            )
        );
        $obEventType->add(
            array(
                "LID" => "en",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_TO",
                "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_TO_NAME_EN"),
                "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_TO_DESCRIPTION_EN"),
            )
        );
        $obEventMessage = new CEventMessage;
        $sites = CSite::GetList($by = "sort", $order = "asc", array("ACTIVE" => "Y"));
        $siteIds = array();
        while ($site = $sites->Fetch()) {
            $siteIds[] = $site["ID"];
        }
        $templateId = $obEventMessage->add(array(
            "ACTIVE" => "Y",
            "EVENT_NAME" => "TR_CA_DOCS_MAIL_TO",
            "LID" => $siteIds,
            "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
            "EMAIL_TO" => "#EMAIL#",
            "SUBJECT" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_TO_SUBJECT"),
            "BODY_TYPE" => "html",
            "MESSAGE" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_TO_BODY"),
        ));
        Option::set("trusted.cryptoarmdocs", "MAIL_TEMPLATE_ID_TO", $templateId);
    }

    function createMailEventShare()
    {
        $obEventType = new CEventType;
        $obEventType->add(
            array(
                "LID" => "ru",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_SHARE",
                "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_SHARE_NAME_RU"),
                "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_SHARE_DESCRIPTION_RU"),
            )
        );
        $obEventType->add(
            array(
                "LID" => "en",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_SHARE",
                "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_SHARE_NAME_EN"),
                "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_SHARE_DESCRIPTION_EN"),
            )
        );
        $obEventMessage = new CEventMessage;
        $sites = CSite::GetList($by = "sort", $order = "asc", array("ACTIVE" => "Y"));
        $siteIds = array();
        while ($site = $sites->Fetch()) {
            $siteIds[] = $site["ID"];
        }
        $templateId = $obEventMessage->add(array(
            "ACTIVE" => "Y",
            "EVENT_NAME" => "TR_CA_DOCS_MAIL_SHARE",
            "LID" => $siteIds,
            "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
            "EMAIL_TO" => "#EMAIL#",
            "SUBJECT" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_SHARE_SUBJECT"),
            "BODY_TYPE" => "html",
            "MESSAGE" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_SHARE_BODY"),
        ));
        Option::set("trusted.cryptoarmdocs", "MAIL_TEMPLATE_ID_SHARE", $templateId);
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        $step = (int)$request["step"];

        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("MOD_UNINSTALL_TITLE"),
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/unstep1.php"
            );
        }
        if ($step == 2) {
            $this->UnInstallFiles();
            $this->UnInstallModuleOptions();
            $savedata = $request["savedata"];
            if ($savedata != "Y") {
                $this->UnInstallDB();
            }
            $this->UnInstallMailEvent();
            ModuleManager::unRegisterModule($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("MOD_UNINSTALL_TITLE"),
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/unstep2.php"
            );
        }
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_by_user/");
        DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_by_order/");
        DeleteDirFilesEx("/bitrix/components/trusted/docs/");
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

    function UnInstallModuleOptions()
    {
        Option::delete(
            "trusted.cryptoarmdocs",
            array("name" => "MAIL_EVENT_ID")
        );
        Option::delete(
            "trusted.cryptoarmdocs",
            array("name" => "MAIL_TEMPLATE_ID")
        );
        Option::delete(
            "trusted.cryptoarmdocs",
            array("name" => "MAIL_EVENT_ID_TO")
        );
        Option::delete(
            "trusted.cryptoarmdocs",
            array("name" => "MAIL_TEMPLATE_ID_TO")
        );
        Option::delete(
            "trusted.cryptoarmdocs",
            array("name" => "MAIL_EVENT_ID_SHARE")
        );
        Option::delete(
            "trusted.cryptoarmdocs",
            array("name" => "MAIL_TEMPLATE_ID_SHARE")
        );
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
        $sql = "DROP TABLE IF EXISTS `tr_ca_docs`";
        $DB->Query($sql);
    }

    function dropTableDocumentProps()
    {
        global $DB;
        $sql = "DROP TABLE IF EXISTS `tr_ca_docs_property`";
        $DB->Query($sql);
    }

    function UnInstallMailEvent()
    {
        $this->UnInstallMailEventByOrder();
        $this->UnInstallMailEventTo();
        $this->UnInstallMailEventShare();
    }

    function UnInstallMailEventByOrder() {
        $by = "id";
        $order = "desc";
        $eventMessages = CEventMessage::GetList($by, $order, array("TYPE" => "TR_CA_DOCS_MAIL_BY_ORDER"));
        $eventMessage = new CEventMessage;
        while ($template = $eventMessages->Fetch()) {
            $eventMessage->Delete((int)$template["ID"]);
        }
        $obEventType = new CEventType;
        $obEventType->Delete("TR_CA_DOCS_MAIL_BY_ORDER");
    }

    function UnInstallMailEventTo() {
        $by = "id";
        $order = "desc";
        $eventMessages = CEventMessage::GetList($by, $order, array("TYPE" => "TR_CA_DOCS_MAIL_TO"));
        $eventMessage = new CEventMessage;
        while ($template = $eventMessages->Fetch()) {
            $eventMessage->Delete((int)$template["ID"]);
        }
        $obEventType = new CEventType;
        $obEventType->Delete("TR_CA_DOCS_MAIL_TO");
    }

    function UnInstallMailEventShare() {
        $by = "id";
        $order = "desc";
        $eventMessages = CEventMessage::GetList($by, $order, array("TYPE" => "TR_CA_DOCS_MAIL_SHARE"));
        $eventMessage = new CEventMessage;
        while ($template = $eventMessages->Fetch()) {
            $eventMessage->Delete((int)$template["ID"]);
        }
        $obEventType = new CEventType;
        $obEventType->Delete("TR_CA_DOCS_MAIL_SHARE");
    }

    function dropDocumentChain($id)
    {
        global $DB;
        // Try to find parent doc
        $sql = 'SELECT `PARENT_ID` FROM `tr_ca_docs` WHERE `ID`=' . $id;
        $res = $DB->Query($sql)->Fetch();
        $parentId = $res["PARENT_ID"];

        $sql = 'DELETE FROM `tr_ca_docs`'
            . 'WHERE ID = ' . $id;
        $DB->Query($sql);
        $sql = 'DELETE FROM `tr_ca_docs_property`'
            . 'WHERE DOCUMENT_ID = ' . $id;
        $DB->Query($sql);

        if ($parentId) {
            $this->dropDocumentChain($parentId);
        }
    }

}

