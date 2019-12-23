<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Trusted\CryptoARM\Docs;

//checks the name of currently installed core from highest possible version to lowest
$coreIds = array(
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
);
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/" . $coreId . "/";
    if(file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/include.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/Database.php';
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php";

Loc::loadMessages(__FILE__);

/*fBs*/Class trusted_cryptoarmdocscrp extends CModule/*fMs*/ //tags for core name changing script
{
    // Required by the marketplace standards

    /*fBs*/var $MODULE_ID = "trusted.cryptoarmdocscrp";/*fMs*/ //tags for core name changing script
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    /*fBs*/ function trusted_cryptoarmdocscrp()/*fMs*/ //tags for core name changing script
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
            } elseif ($request["dropDBandIB"] == "Y") {
                $this->UnInstallDB();
                if (IsModuleInstalled("trusted.cryptoarmdocsforms")) {
                    trusted_cryptoarmdocsforms::UnInstallIb();
                }
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
            $this->InstallMailEvents();

            ModuleManager::registerModule($this->MODULE_ID);

            $modulesNeeded = array("trusted.id");
            $modulesForSmallBusiness = array('trusted.cryptoarmdocsorders', 'trusted.cryptoarmdocsforms');
            $modulesForCorportal = array('trusted.cryptoarmdocsbp');

            $errorMessage = "";
            $stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");
            $arUpdateList = CUpdateClient::GetUpdatesList($errorMessage, LANG, $stableVersionsOnly);
            $bitrixRedaction = $arUpdateList["CLIENT"][0]["@"]["LICENSE"];

            switch($bitrixRedaction) {
                case (stristr($bitrixRedaction, Loc::GetMessage('TR_CA_DOCS_SMALL_BUSINESS_OR_BUSINESS_REDACTION')) != null):
                    $modulesNeeded = array_merge($modulesNeeded, $modulesForSmallBusiness);
                    break;
                case (stristr($bitrixRedaction, Loc::GetMessage('TR_CA_DOCS_CORP_REDACTION')) != null):
                case (stristr($bitrixRedaction, Loc::GetMessage('TR_CA_DOCS_ENTERPRISE_REDACTION')) != null):
                    $modulesNeeded = array_merge($modulesNeeded, $modulesForSmallBusiness, $modulesForCorportal);
                    break;
            }

            if ($modulesNeeded) {
                $modulesOutOfDate = array();
                $modulesWereNotInstalled = array();
                foreach($modulesNeeded as $moduleName){
                    $modulesPathDir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleName."/";
                    $moduleDownloaded = true;
                    if(!file_exists($modulesPathDir)) {
                        $strError = '';
                        $moduleDownloaded = CUpdateClientPartner::LoadModuleNoDemand($moduleName,$strError,'N',false);
                        CModule::CreateModuleObject($moduleName);
                        if (!$moduleDownloaded) {
                            $modulesWereNotInstalled[] = $moduleName;
                        }
                    }

                    if ($moduleDownloaded) {
                        $className = str_replace(".", "_", $moduleName);
                        if (!IsModuleInstalled($moduleName) && $className::CoreAndModuleAreCompatible()==="ok") {
                            $className::DoInstall();
                        } elseif (IsModuleInstalled($moduleName) && $className::CoreAndModuleAreCompatible()!=="ok") {
                            $modulesOutOfDate[] = $moduleName;
                        }
                    }
                }

                if ($modulesOutOfDate || $modulesWereNotInstalled) {
                    Option::set(TR_CA_DOCS_MODULE_ID, TR_CA_DOCS_MODULES_OUT_OF_DATE, implode(", ", $modulesOutOfDate));
                    Option::set(TR_CA_DOCS_MODULE_ID, TR_CA_DOCS_MODULES_WERE_NOT_INSTALLED, implode(", ", $modulesWereNotInstalled));
                    $APPLICATION->IncludeAdminFile(
                        Loc::getMessage("MOD_INSTALL_TITLE"),
                        $DOCUMENT_ROOT . "/bitrix/modules/" . $this->MODULE_ID . "/install/step_some_modules_out_of_date.php"
                    );
                }
            }
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

    function bizprocSupport()
    {
        return IsModuleInstalled("bizproc");
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
            'MAIL_EVENT_ID_REQUIRED_SIGN' => 'TR_CA_DOCS_MAIL_REQUIRED_SIGN',
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

        $sql = "CREATE TABLE IF NOT EXISTS `tr_ca_docs_require` (
                    `ID` int(11) NOT NULL AUTO_INCREMENT,
                    `DOCUMENT_ID` int(11) DEFAULT NULL,
                    `USER_ID` int(11) DEFAULT NULL,
                    `EMAIL_STATUS` varchar(8) COLLATE utf8_unicode_ci DEFAULT 'NOT_SENT',
                    `SIGNED` tinyint(1) DEFAULT '0',
                PRIMARY KEY (`ID`),
                KEY `fk_tr_ca_docs_require_tr_ca_docs_idx` (`DOCUMENT_ID`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->Query($sql);
    }

     function InstallIb() {
         if (IsModuleInstalled("trusted.cryptoarmdocsforms")) {
             trusted_cryptoarmdocsforms::InstallIb();
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

            // required sign
            array(
                "LID" => "ru",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_REQUIRED_SIGN",
                "NAME" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_REQUIRED_SIGN_NAME"),
                "DESCRIPTION" => Loc::getMessage("TR_CA_DOCS_MAIL_EVENT_REQUIRED_SIGN_DESCRIPTION"),
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

            // send require sign
            'MAIL_TEMPLATE_ID_REQUIRED_SIGN' => array(
                "ACTIVE" => "Y",
                "EVENT_NAME" => "TR_CA_DOCS_MAIL_REQUIRED_SIGN",
                "LID" => $siteIds,
                "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
                "EMAIL_TO" => "#EMAIL#",
                "SUBJECT" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_REQUIRED_SIGN_SUBJECT"),
                "BODY_TYPE" => "html",
                "MESSAGE" => Loc::getMessage("TR_CA_DOCS_MAIL_TEMPLATE_REQUIRED_SIGN_BODY"),
            ),
        );
        foreach ($templates as $templateName => $template) {
            $templateId = $obEventMessage->add($template);
            Option::set($this->MODULE_ID, $templateName, $templateId);
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

            $this->UnInstallModuleOptions();

            $deleteiblocks = $request["deleteiblocks"];
            if ($deleteiblocks == "Y") {
                trusted_cryptoarmdocsforms::UnInstallIb();
            }

            $deletedata = $request["deletedata"];
            if ($deletedata == "Y") {
                $this->UnInstallDB();

            }

            $this->UnInstallMailEvents();

            $deletedata = $request["deletemodules"];
            if ($deletedata == "Y") {
                if (IsModuleInstalled('trusted.cryptoarmdocsbp')) {
                    CModule::includeModule('trusted.cryptoarmdocsbp');
                    trusted_cryptoarmdocsbp::DoUninstall();
                }
                if (IsModuleInstalled('trusted.cryptoarmdocsforms')) {
                    CModule::includeModule('trusted.cryptoarmdocsforms');
                    trusted_cryptoarmdocsforms::DoUninstall();
                }
                if (IsModuleInstalled('trusted.cryptoarmdocsorders')) {
                    CModule::includeModule('trusted.cryptoarmdocsorders');
                    trusted_cryptoarmdocsorders::DoUninstall();
                }
            }

            $this->UnInstallFiles();
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
        //DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_by_order/");
        // if ($this->crmSupport()) {
        //     DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_crm/");
        // }
        // DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_form/");
        DeleteDirFilesEx("/bitrix/components/trusted/cryptoarm_docs_upload/");
        DeleteDirFilesEx("/bitrix/components/trusted/api/");
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
        $options = array(
            // 'DOCUMENTS_DIR',
            'MAIL_EVENT_ID',
            'MAIL_TEMPLATE_ID',
            'MAIL_EVENT_ID_TO',
            'MAIL_TEMPLATE_ID_TO',
            'MAIL_EVENT_ID_SHARE',
            'MAIL_TEMPLATE_ID_SHARE',
            'MAIL_EVENT_ID_REQUIRED_SIGN',
            'MAIL_TEMPLATE_ID_REQUIRED_SIGN',
        );
        foreach ($options as $option) {
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
        $sql = "DROP TABLE IF EXISTS `tr_ca_docs_require`";
        $DB->Query($sql);
    }

    // function UnInstallIb() {
    //     Docs\IBlock::uninstall();
    // }

    function UnInstallMailEvents()
    {
        $events = array(
            'TR_CA_DOCS_MAIL_BY_ORDER',
            'TR_CA_DOCS_MAIL_TO',
            'TR_CA_DOCS_MAIL_SHARE',
            'TR_CA_DOCS_MAIL_REQUIRED_SIGN',
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
}

