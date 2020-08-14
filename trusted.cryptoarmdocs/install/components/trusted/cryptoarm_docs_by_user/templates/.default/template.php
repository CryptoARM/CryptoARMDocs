<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

//checks the name of currently installed core from highest possible version to lowest
$coreIds = [
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
];
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $coreId . "/";
    if (file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}

$this->addExternalJS("https://cdn.jsdelivr.net/npm/vue/dist/vue.js");
CJSCore::RegisterExt(
    "components",
    [
        "js" => "/bitrix/js/" . $module_id . "/components.js",
    ]
);
CUtil::InitJSCore(['components']);

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$allIds = $arResult['ALL_IDS'];
$allIdsJs = $arResult['ALL_IDS_JS'];
$docs = $arResult['DOCS'];
$asd = true;

if ($USER->GetFullName()) {
    $compTitle = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOCS_BY_ORDER") . $USER->GetFullName();
} else {
    $compTitle = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOCS_BY_ORDER") . $USER->GetEmail();
}

$zipName = $compTitle . " " . date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time());
$comp_id = Docs\Utils::generateUUID() ;
?>

<a id="trca-reload-doc" href="<?= $_SERVER["REQUEST_URI"] ?>"></a>

<div id="trca-docs-by-user_<?= $comp_id?>">
    <trca-docs>
        <header-title title="<?= $compTitle ?>">
            <?
            if (!empty($allIds)) {
                ?>
                <header-menu id="trca-docs-header-menu-by-user">
                    <header-menu-button icon="help"
                                        :id="<?= $allIdsJs ?>"
                                        @button-click="verifySome"
                                        data-id="data-verify-some"
                                        message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_VERIFY_SOME"); ?>">
                    </header-menu-button>
                    <header-menu-button icon="create"
                                        :id="<?= $allIdsJs ?>"
                                        role="CLIENT"
                                        @button-click="signSome"
                                        data-id="data-sign-some"
                                        message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SIGN_SOME"); ?>">
                    </header-menu-button>
                    <header-menu-button icon="file_download"
                                        zipname="<?= $zipName ?>"
                                        :id="<?= $allIdsJs ?>"
                                        @button-click="downloadSome"
                                        data-id="data-download-some"
                                        message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOWNLOAD_SOME"); ?>">
                    </header-menu-button>
                    <header-menu-button icon="email"
                                        :id="<?= $allIdsJs ?>"
                                        @button-click="sendSome"
                                        data-id="data-send-some"
                                        message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SEND_DOCS_SOME"); ?>">
                    </header-menu-button>
                    <?
                    if ($arParams["ALLOW_REMOVAL"] === 'Y') {
                        ?>
                        <header-menu-button icon="delete"
                                            :id="<?= $allIdsJs ?>"
                                            @button-click="removeSome"
                                            data-id="data-remove-some"
                                            message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DELETE_SOME"); ?>">
                        </header-menu-button>
                        <?
                    }
                    ?>
                </header-menu>
                <?
            }
            ?>
        </header-title>
        <docs-header title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOC") ?>"
                     date="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_TIMESTAMP") ?>"
                     id="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_IDN") ?>"
        ></docs-header>
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
                    $mustToSign = $doc["MUST_TO_SIGN"];
                    $sharedStatus = $doc["SHARED_STATUS_JS"];

                    if ($docType == DOC_TYPE_SIGNED_FILE) {
                        if ($docStatus == DOC_STATUS_BLOCKED) {
                            $icon = "lock";
                            $iconCss = "color: red";
                        } else {
                            if ($mustToSign) {
                                $icon = "create";
                                $iconCss = "color: rgb(33, 150, 243)";
                            } else {
                                $icon = "done_all";
                                $iconCss = "color: green";
                            }
                        }
                    } else {
                        switch ($docStatus) {
                            case DOC_STATUS_NONE:
                                $icon = $mustToSign ? "create" : "";
                                $iconCss = "color: rgb(33, 150, 243)";
                                break;
                            case DOC_STATUS_BLOCKED:
                                $icon = "lock";
                                $iconCss = "color: red";
                                break;
                            case DOC_STATUS_CANCELED:
                                $icon = "check";
                                $iconCss = "color: red";
                                break;
                            case DOC_STATUS_ERROR:
                                $icon = "error";
                                $iconCss = "color: red";
                                break;
                        }
                    }
                    ?>
                    <docs-items id="check_<?= $docId ?>"
                                docname="<?= $docName ?>"
                                currentUserAccess='<?= $docAccessLevel ?>'
                                :sharedstatus='<?= $sharedStatus ?>'
                                title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_MODAL_INFO"); ?>"
                                @button-click= "showInfoWindow"
                                sendSome=<?= $asd ?>>
                        <doc-name color="<?= $iconCss ?>"
                                  icon="<?= $icon ?>"
                                  name="<?= $doc["NAME"] ?>"
                                  description="<?
                                  if ($docStatus === DOC_STATUS_BLOCKED) {
                                      echo $doc['STATUS_STRING'];
                                  } else {
                                      if ($mustToSign) {
                                          echo Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_MUST_TO_SIGN");
                                      } else {
                                          echo $doc['TYPE_STRING'];
                                      }
                                  } ?>">
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

                        <doc-info info="<?= $docCreated ?>"
                                  title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_TIMESTAMP"); ?>">
                        </doc-info>
                        <doc-info info="<?= $doc["ORIGINAL_ID"] ?>"
                                  title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_ID"); ?>">
                        </doc-info>

                        <doc-buttons component="by_user">
                            <? if ($doc["TYPE"] == "1") { ?>
                                <doc-button icon="help"
                                                 :id="<?= $docId ?>"
                                                 docname="<?= $docName ?>"
                                                 @button-click="verify"
                                                 data-id="data-verify"
                                                 title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_VERIFY"); ?>">
                                </doc-button>
                                <?
                            }
                            ?>

                            <?
                            if ($docAccessLevel == "SIGN" || $docAccessLevel == "OWNER") {
                                ?>
                                <doc-button icon="create"
                                            :id="<?= $docId ?>"
                                            role="CLIENT"
                                            @button-click="sign"
                                            data-id="data-sign"
                                            title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SIGN"); ?>">
                                </doc-button>
                                <?
                            }
                            ?>

                            <doc-button icon="file_download"
                                        :id="<?= $docId ?>"
                                        @button-click="download"
                                        data-id="data-download"
                                        title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOWNLOAD"); ?>">
                            </doc-button>

                            <doc-button icon="info"
                                        :id="<?= $docId ?>"
                                        @button-click="protocol"
                                        data-id="data-protocol"
                                        title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_PROTOCOL"); ?>">
                            </doc-button>

                            <doc-menu icon="share"
                                      id="trca-docs-share-menu-by-user-<?= $docId ?>"
                                      title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SEND"); ?>">
                                <doc-menu-button icon="email"
                                                 :id="<?= $docId ?>"
                                                 @button-click="sendEmail"
                                                 data-id="data-email"
                                                 message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SEND_BY_EMAIL"); ?>"
                                                 title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SEND_BY_EMAIL"); ?>">
                                </doc-menu-button>
                                <?
                                if ($docAccessLevel == "OWNER") {
                                    ?>
                                    <doc-menu-button icon="supervisor_account"
                                                     :id="<?= $docId ?>"
                                                     @button-click="share"
                                                     data-id="data-share"
                                                     message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SHARE"); ?>"
                                                     title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SHARE_TITLE"); ?>">
                                    </doc-menu-button>
                                    <doc-menu-button icon="reply_all"
                                                     :id="<?= $docId ?>"
                                                     @button-click="requireToSign"
                                                     data-id="data-requireToSign"
                                                     message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SIGN_REQUEST"); ?>"
                                                     title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SIGN_REQUEST_TITLE"); ?>">
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
                                                data-id="data-remove"
                                                title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_REMOVE"); ?>">
                                    </doc-button>
                                    <?
                                }
                            } elseif ($docAccessLevel === "SIGN" || $docAccessLevel === "READ") {
                                ?>
                                <doc-button icon="close"
                                            :id="<?= $docId ?>"
                                            @button-click="unshare"
                                            data-id="data-unshare"
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
                $maxSize = Docs\Utils::maxUploadFileSize();
                ?>
                <docs-upload-file maxSize="<?= $maxSize ?>"
                                  title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_ADD"); ?>"
                                  value="<?= Docs\Utils::currUserId() ?>">
                </docs-upload-file>
                <?
            }
        }
        ?>
    </trca-docs>
</div>

<script>
    var blockedDocIds = <?= $arResult["BLOCKED_DOCUMENTS"]["IDS"]?>;
    var blockedDocTokens = <?= $arResult["BLOCKED_DOCUMENTS"]["TOKENS"]?>;

    if (blockedDocTokens) {
        if (!$('#trca-modal-overlay').length > 0) {
            trustedCA.showModalWindow(blockedDocIds);
            var interval = setInterval(() => trustedCA.blockCheck(blockedDocTokens, interval, null), 5000);
        }
    }

    new Vue({
        el: '#trca-docs-by-user_<?= $comp_id?>',
        methods: {
            getChecked: function () {
                let ids = new Array;
                $('input[id^="check_"]').each(function(){
                    if($(this).prop("checked")) {
                        let idStr = $(this).attr("id");
                        let id = idStr.replace("check_", "");
                        ids.push(id);
                    };
                });
                return ids;
            },
            sendEmail: function (id) {
                let object = new Object();
                trustedCA.promptAndSendEmail(id, 'MAIL_EVENT_ID_TO', object, 'MAIL_TEMPLATE_ID_TO');
            },
            sendSome: function () {
                let object = new Object();
                let ids = new Array;
                ids = this.getChecked();
                trustedCA.promptAndSendEmail(ids, 'MAIL_EVENT_ID_TO', object, 'MAIL_TEMPLATE_ID_TO');
            },
            sign: function (id, role) {
                trustedCA.sign(id, JSON.parse('{"role": "${role}"}'));
            },
            signSome: function (role) {
                let ids = new Array;
                ids = this.getChecked();
                trustedCA.sign(ids, JSON.parse('{"role": "${role}"}'));
            },
            verify: function (id) {
                trustedCA.verify(id);
            },
            verifySome: function () {
                let ids = new Array;
                ids = this.getChecked();
                trustedCA.verify(ids);
            },
            download: function (id, zipname) {
                trustedCA.download(id, zipname);
            },
            downloadSome: function (zipname) {
                let ids = new Array;
                ids = this.getChecked();
                trustedCA.download(ids, zipname);
            },
            protocol: function (idAr) {
                id = idAr[0];
                trustedCA.protocol(id);
            },
            share: function (id) {
                trustedCA.promptAndShare(id, 'SHARE_SIGN');
            },
            remove: function (id) {
                trustedCA.remove(id, false, trustedCA.reloadDoc);
            },
            removeSome: function() {
                let ids = new Array;
                ids = this.getChecked();
                trustedCA.remove(ids, false, trustedCA.reloadDoc)
            },
            unshare: function (id) {
                trustedCA.unshare(id, null, false, trustedCA.reloadDoc);
            },
            requireToSign: function (id) {
                trustedCA.promptAndRequireToSign(id);
            },
            showInfoWindow: function (id, docname, sharedstatus, currentuseraccess) {
                trustedCA.showInfoModalWindow(id, docname, sharedstatus, currentuseraccess);
            }
        }
    })
</script>

