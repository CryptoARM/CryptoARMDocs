<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Trusted\CryptoARM\Docs;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocs/include.php';

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
                $this->UnInstallIb();
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
            $this->InstallIb();
            $this->InstallMenuItems();
            $this->InstallMailEvents();
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

    function crmSupport()
    {
        return IsModuleInstalled("crm");
    }

    function InstallFiles()
    {
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/components/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/",
            true, true
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/admin/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/",
            true, false
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/js/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/",
            true, true
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/",
            true, true
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/activities/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/activities/custom/",
            true, true
        );
        if ($this->crmSupport()) {
            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/crm_pub/",
                $_SERVER["DOCUMENT_ROOT"],
                true, true
            );
            CUrlRewriter::Add(
                array(
                    'CONDITION' => '#^/tr_ca_docs/#',
                    'RULE' => '',
                    'ID' => 'trusted:cryptoarm_docs_crm',
                    'PATH' => '/tr_ca_docs/index.php',
                )
            );
        }
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
        $options = array(
            'DOCUMENTS_DIR' => '/docs/',
            'MAIL_EVENT_ID' => 'TR_CA_DOCS_MAIL_BY_ORDER',
            'MAIL_EVENT_ID_TO' => 'TR_CA_DOCS_MAIL_TO',
            'MAIL_EVENT_ID_SHARE' => 'TR_CA_DOCS_MAIL_SHARE',
        );
        foreach ($options as $name => $value) {
            if (!Option::get($this->MODULE_ID, $name, '')) {
                Option::set($this->MODULE_ID, $name, $value);
            }
        }
    }

    function InstallDB()
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
                    `BLOCK_TIME` datetime DEFAULT '1000-01-01 00:00:00',
                    `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`ID`),
                KEY `fk_tr_ca_docs_tr_ca_docs1_idx` (`PARENT_ID`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->Query($sql);

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

    function InstallIb() {
        Docs\IBlock::install();
    }

    function InstallMenuItems() {
        $siteInfo = $this->getSiteInfo();

        if ($this->crmSupport()) {
            $this->AddMenuItem(
                $siteInfo["DIR"] . ".top.menu.php",
                array(
                    Loc::getMessage('TR_CA_DOCS_CRM_MENU_TITLE'),
                    $siteInfo["DIR"] . "tr_ca_docs/",
                    array(),
                    array(),
                    "IsModuleInstalled('" . $this->MODULE_ID . "')"
                ),
                $siteInfo["LID"]
            );
        }
    }

    function InstallMailEvents()
    {
        $obEventType = new CEventType;
        $events = array(
            // by order
            array(
                "LID" => "ru",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_BY_ORDER",
                "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_NAME"),
                "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_DESCRIPTION"),
            ),

            // to
            array(
                "LID" => "ru",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_TO",
                "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_TO_NAME"),
                "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_TO_DESCRIPTION"),
            ),

            // share
            array(
                "LID" => "ru",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_SHARE",
                "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_SHARE_NAME"),
                "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_SHARE_DESCRIPTION"),
            ),
        );
        foreach ($events as $event) {
            $obEventType->add($event);
        }

        $obEventMessage = new CEventMessage;
        $sites = CSite::GetList($by = "sort", $order = "asc", array("ACTIVE" => "Y"));
        $siteIds = array();
        while ($site = $sites->Fetch()) {
            $siteIds[] = $site["ID"];
        }
        $templates = array(
            // by order
            'MAIL_TEMPLATE_ID' => array(
                "ACTIVE" => "Y",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_BY_ORDER",
                "LID" => $siteIds,
                "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
                "EMAIL_TO" => "#EMAIL#",
                "SUBJECT" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_SUBJECT"),
                "BODY_TYPE" => "html",
                "MESSAGE" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_BODY"),
            ),

            // to
            'MAIL_TEMPLATE_ID_TO' => array(
                "ACTIVE" => "Y",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_TO",
                "LID" => $siteIds,
                "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
                "EMAIL_TO" => "#EMAIL#",
                "SUBJECT" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_TO_SUBJECT"),
                "BODY_TYPE" => "html",
                "MESSAGE" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_TO_BODY"),
            ),

            // share
            'MAIL_TEMPLATE_ID_SHARE' => array(
                "ACTIVE" => "Y",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_SHARE",
                "LID" => $siteIds,
                "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
                "EMAIL_TO" => "#EMAIL#",
                "SUBJECT" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_SHARE_SUBJECT"),
                "BODY_TYPE" => "html",
                "MESSAGE" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_SHARE_BODY"),
            ),
        );
        foreach ($templates as $templateName => $template) {
            $templateId = $obEventMessage->add($template);
            Option::set("trusted.cryptoarmdocs", $templateName, $templateId);
        }
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
                $this->UnInstallIb();
            }
            $this->UnInstallMenuItems();
            $this->UnInstallMailEvents();
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
        DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_crm/");
        DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_upload/");
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
        if ($this->crmSupport()) {
            DeleteDirFilesEx("/tr_ca_docs/");
            DeleteDirFilesEx("/bitrix/activities/custom/trustedcasign/");
            DeleteDirFilesEx("/bitrix/activities/custom/trustedcaapprove/");
            CUrlRewriter::Delete(
                array(
                    'ID' => 'trusted:cryptoarm_docs_crm',
                    'PATH' => '/tr_ca_docs/index.php',
                )
            );
        }
        return true;
    }

    function UnInstallModuleOptions()
    {
        $options = array(
            // 'DOCUMENTS_DIR',
            'MAIL_EVENT_ID',
            'MAIL_TEMPLATE_ID',
            'MAIL_EVENT_ID_TO',
            'MAIL_TEMPLATE_ID_TO',
            'MAIL_EVENT_ID_SHARE',
            'MAIL_TEMPLATE_ID_SHARE',
        );
        foreach ($options as $options) {
            Option::delete(
                $this->MODULE_ID,
                array('name' => $option)
            );
        }
    }

    function UnInstallDB()
    {
        global $DB;
        if (Loader::includeModule('bizproc')) {
            $docs = Docs\Database::getDocuments();
            foreach ($docs->getList() as $doc) {
                $doc->remove();
            }
        }
        $sql = "DROP TABLE IF EXISTS `tr_ca_docs`";
        $DB->Query($sql);
        $sql = "DROP TABLE IF EXISTS `tr_ca_docs_property`";
        $DB->Query($sql);
    }

    function UnInstallIb() {
        Docs\IBlock::uninstall();
    }

    function UnInstallMenuItems() {
        $siteInfo = $this->getSiteInfo();

        if ($this->crmSupport()) {
            $this->DeleteMenuItem(
                $siteInfo["DIR"] . ".top.menu.php",
                $siteInfo["DIR"] . "tr_ca_docs/",
                $siteInfo["LID"]
            );
        }
    }

    function UnInstallMailEvents()
    {
        $events = array(
            'TR_CA_DOCS_MAIL_BY_ORDER',
            'TR_CA_DOCS_MAIL_TO',
            'TR_CA_DOCS_MAIL_SHARE',
        );
        foreach ($events as $event) {
            $eventMessages = CEventMessage::GetList(
                $by = 'id',
                $order = 'desc',
                array('TYPE' => $event)
            );
            $eventMessage = new CEventMessage;
            while ($template = $eventMessages->Fetch()) {
                $eventMessage->Delete((int)$template['ID']);
            }
            $eventType = new CEventType;
            $eventType->Delete($event);
        }
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

    function getSiteInfo() {
        $siteID = CSite::GetDefSite();
        return CSite::GetByID($siteID)->Fetch();
    }

    function AddMenuItem($menuFile, $menuItem,  $siteID, $pos = -1)
    {
        if (CModule::IncludeModule('fileman')) {
            $arResult = CFileMan::GetMenuArray(Application::getDocumentRoot() . $menuFile);
            $arMenuItems = $arResult["aMenuLinks"];
            $menuTemplate = $arResult["sMenuTemplate"];

            $bFound = false;
            foreach ($arMenuItems as $item) {
                if ($item[1] == $menuItem[1]) {
                    $bFound = true;
                    break;
                }
            }

            if (!$bFound) {
                if ($pos<0 || $pos>=count($arMenuItems)) {
                    $arMenuItems[] = $menuItem;
                } else {
                    for ($i=count($arMenuItems); $i>$pos; $i--) {
                        $arMenuItems[$i] = $arMenuItems[$i-1];
                    }
                    $arMenuItems[$pos] = $menuItem;
                }

                CFileMan::SaveMenu(array($siteID, $menuFile), $arMenuItems, $menuTemplate);
            }
        }
    }

    function DeleteMenuItem($menuFile, $menuLink, $siteID) {
        if (CModule::IncludeModule("fileman")) {
            $arResult = CFileMan::GetMenuArray(Application::getDocumentRoot() . $menuFile);
            $arMenuItems = $arResult["aMenuLinks"];
            $menuTemplate = $arResult["sMenuTemplate"];

            foreach($arMenuItems as $key => $item) {
                if($item[1] == $menuLink) unset($arMenuItems[$key]);
            }

            CFileMan::SaveMenu(array($siteID, $menuFile), $arMenuItems, $menuTemplate);
        }
    }

}

