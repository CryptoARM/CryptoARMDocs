<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

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

$this->addExternalJS("https://cdn.jsdelivr.net/npm/vue/dist/vue.js");
CJSCore::RegisterExt(
    "components",
    array(
        "js" => "/bitrix/js/" . $module_id . "/components.js",
    )
);
CUtil::InitJSCore(array('components'));

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$allIds = $arResult['ALL_IDS'];
$allIdsJs = $arResult['ALL_IDS_JS'];
$docs = $arResult['DOCS'];

if ($USER->GetFullName()) {
    $compTitle = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOCS_BY_ORDER") . $USER->GetFullName();
} else {
    $compTitle = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOCS_BY_ORDER") . $USER->GetEmail();
 }

$zipName = $compTitle . " " . date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time());
?>

<a id="trca-reload-doc" href="<?= $_SERVER["REQUEST_URI"] ?>"></a>

<?
$APPLICATION->IncludeComponent(
    'trusted:cryptoarm_docs_upload',
    '.default',
    array(
        'FILES' => array('tr_ca_upload_comp_by_user'),
        'PROPS' => array(
            'USER' => $USER->GetID(),
        ),
    ),
    false
);
?>

<div id="trca-docs-by-user">
    <trca-docs>
        <header-title title="<?= $compTitle ?>">
            <?
            if (!empty($allIds)) {
                ?>
                <header-menu id="trca-docs-header-menu-by-user">
                    <header-menu-button icon="email"
                                        :id="<?= $allIdsJs ?>"
                                        @button-click="sendEmail"
                                        message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SEND_DOCS_ALL"); ?>">
                    </header-menu-button>
                    <header-menu-button icon="create"
                                        :id="<?= $allIdsJs ?>"
                                        @button-click="sign"
                                        message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SIGN_ALL"); ?>">
                    </header-menu-button>
                    <header-menu-button icon="info"
                                        :id="<?= $allIdsJs ?>"
                                        @button-click="verify"
                                        message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_VERIFY_ALL"); ?>">
                    </header-menu-button>
                    <header-menu-button icon="save_alt"
                                        onclick="<?= "trustedCA.download($allIdsJs, '$zipName')"?>"
                                        message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOWNLOAD_ALL"); ?>">
                    </header-menu-button>
                <?
                if ($arParams["ALLOW_REMOVAL"] === 'Y') {
                ?>
                    <header-menu-button icon="delete"
                                        :id="<?= $allIdsJs ?>"
                                        @button-click="remove"
                                        message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DELETE_ALL"); ?>">
                    </header-menu-button>
                <?
                }
                ?>
                </header-menu>
                <?
            }
        ?>
        </header-title>
        <docs-content>
            <?
            if (is_array($docs)) {
                foreach ($docs as $doc) {
                    $docId = $doc["ID"];
                    $docType = $doc["TYPE"];
                    $docStatus = $doc["STATUS"];
                    $docAccessLevel = $doc["ACCESS_LEVEL"];
                    $docName = $doc["NAME"];
                    $docCreated = $doc["DATE_CREATED"];

                    if ($docType == DOC_TYPE_SIGNED_FILE) {
                        if ($docStatus == DOC_STATUS_BLOCKED){
                            $icon = "lock";
                            $iconCss = "color: red";
                        } else {
                            $icon = "check_circles";
                            $iconCss = "color: rgb(33, 150, 243)";
                        }
                    } else {
                        switch ($docStatus) {
                            case DOC_STATUS_NONE:
                                $icon = "insert_drive_file";
                                $iconCss = "color: green";
                                break;
                            case DOC_STATUS_BLOCKED:
                                $icon = "lock";
                                $iconCss = "color: red";
                                break;
                            case DOC_STATUS_CANCELED:
                                $icon = "insert_drive_file";
                                $iconCss = "color: red";
                                break;
                            case DOC_STATUS_ERROR:
                                $icon = "error";
                                $iconCss = "color: red";
                                break;
                        }
                    }
                ?>
                <docs-items>
                    <doc-name color="<?= $iconCss ?>"
                        icon="<?= $icon ?>"
                        name="<?= $doc["NAME"] ?>"
                        description="<?
                            if ($docStatus === DOC_STATUS_BLOCKED) {
                                echo $doc['STATUS_STRING'];
                            } else {
                                echo $doc['TYPE_STRING'];
                            }
                        ?>">
                        <doc-name-owner owner="<?
                            echo Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_OWNER");
                            if ($docAccessLevel == "OWNER") {
                                echo Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_OWNER2");
                            } else {
                                echo $doc['OWNER_USERNAME'];
                            }
                        ?>">
                        </doc-name-owner>
                    </doc-name>
                    <doc-buttons>
                        <doc-info info="<?= $docCreated ?>"
                        title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_TIMESTAMP"); ?>">
                        </doc-info>
                        <doc-info info="<?= $docId ?>"
                        title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_ID"); ?>">
                        </doc-info>
                        <doc-info-button icon="info_outline"
                                    :id="<?= $docId ?>"
                                    docname="<?= $docName ?>"
                                    @button-click="verify"
                                    title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_VERIFY"); ?>">
                        </doc-info-button>

                        <?
                        if ($docAccessLevel == "SIGN" || $docAccessLevel == "OWNER") {
                        ?>
                        <doc-button icon="create"
                                    :id="<?= $docId ?>"
                                    @button-click="sign"
                                    title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SIGN"); ?>">
                        </doc-button>
                        <?
                        }
                        ?>

                        <doc-button icon="save_alt"
                                    :id="<?= $docId ?>"
                                    @button-click="download"
                                    title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOWNLOAD"); ?>">
                        </doc-button>

                        <doc-menu icon="share"
                            id="trca-docs-share-menu-by-user-<?=$docId?>"
                            title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SEND"); ?>">
                            <doc-menu-button icon="email"
                                :id="<?= $docId ?>"
                                @button-click="sendEmail"
                                message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SEND_BY_EMAIL"); ?>">
                            </doc-menu-button>
                            <?
                            if ($docAccessLevel == "OWNER") {
                            ?>
                                <doc-menu-button icon="share"
                                    :id="<?= $docId ?>"
                                    @button-click="share"
                                    message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SHARE"); ?>">
                                </doc-menu-button>
                                <doc-menu-button icon="supervisor_account"
                                    :id="<?= $docId ?>"
                                    @button-click="requireToSign"
                                    message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SIGN_REQUEST"); ?>">
                                </doc-menu-button>
                            <?
                            }
                            ?>
                        </doc-menu>
                        <?
                        if ($docAccessLevel == "OWNER") {
                        ?>
                            <?
                            if ($arParams["ALLOW_REMOVAL"] === 'Y') {
                            ?>
                                <doc-button icon="delete"
                                            :id="<?= $docId ?>"
                                            @button-click="remove"
                                            title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_REMOVE"); ?>">
                                </doc-button>
                        <?
                            }
                        } elseif ($docAccessLevel === "SIGN" || $docAccessLevel === "READ") {
                        ?>
                        <doc-button icon="close"
                                    :id="<?= $docId ?>"
                                    @button-click="unshare"
                                    title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_UNSHARE"); ?>">
                        </doc-button>
                        <?
                        }
                        ?>
                    </doc-buttons>
                </docs-items>
                <?
                }
            }
            ?>
        </docs-content>
        <?
        if ($arParams["ALLOW_ADDING"] === 'Y') {
            if ($USER->IsAuthorized()) {
                $maxSize  = Docs\Utils::maxUploadFileSize();
                ?>
                <docs-upload-file maxSize="<?= $maxSize ?>"
                                  title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_ADD"); ?>">
                </docs-upload-file>
                <?
            }
        }
        ?>
    </trca-docs>
</div>

<script>
   new Vue({
        el: '#trca-docs-by-user',
        methods: {
            sendEmail: function(id) {
                let object = new Object ();
                trustedCA.promptAndSendEmail(id, 'MAIL_EVENT_ID_TO', object, 'MAIL_TEMPLATE_ID_TO');
            },
            sign: function(id) {
                trustedCA.sign(id, {role: 'CLIENT'});
            },
            verify: function(id) {
                trustedCA.verify(id);
            },
            download: function(id) {
                trustedCA.download(id, true);
            },
            protocol: function(idAr) {
                id = idAr[0];
                trustedCA.protocol(id)
            },
            share: function(id) {
                trustedCA.promptAndShare(id, 'SHARE_SIGN');
            },
            remove: function(id) {
                trustedCA.remove(id, false, trustedCA.reloadDoc);
            },
            unshare: function(id) {
                trustedCA.unshare(id, false, trustedCA.reloadDoc);
            },
            requireToSign: function(id){
                trustedCA.promptAndRequireToSign(id);
            },
            showInfoWindow: function(id, docname){
                trustedCA.showInfoModalWindow(id, docname)
            }
        }
    })
</script>

