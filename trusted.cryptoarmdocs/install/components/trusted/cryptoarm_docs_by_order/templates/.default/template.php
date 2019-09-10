<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

$this->addExternalJS("https://cdn.jsdelivr.net/npm/vue/dist/vue.js");
CJSCore::RegisterExt(
    "components",
    array(
        "js" => "/bitrix/js/trusted.cryptoarmdocs/components.js",
    )
);
CUtil::InitJSCore(array('components'));

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$allIds = $arResult['ALL_IDS'];
$allIdsJs = $arResult['ALL_IDS_JS'];
$docs = $arResult['DOCS'];

$title = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_DOCS_BY_ORDER") . $arParams["ORDER"];
$zipName = $title . " " . date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time());
?>

<a id="trca-reload-doc" href="<?= $_SERVER["REQUEST_URI"] ?>"></a>

<div id="trca-docs-by-order">
    <trca-docs>
        <header-title title="<?= $title ?>">
            <? if (!empty($allIds)) { ?>
                <header-menu>
                    <header-menu-button icon="email" :id="<?= $allIdsJs ?>" @button-click="sendEmail"
                                       message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_SEND_DOCS_ALL"); ?>">
                    </header-menu-button>
                    <header-menu-button icon="create" :id="<?= $allIdsJs ?>" @button-click="sign"
                                        message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_SIGN_ALL"); ?>">
                    </header-menu-button>
                    <header-menu-button icon="info" :id="<?= $allIdsJs ?>" @button-click="verify"
                                        message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_VERIFY_ALL"); ?>">
                    </header-menu-button>
                    <header-menu-button icon="save_alt" onclick="<?= "trustedCA.download($allIdsJs, '$zipName')"?>"
                                        message="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_DOWNLOAD_ALL"); ?>">
                    </header-menu-button>
                </header-menu>
            <? } ?>
        </header-title>
        <docs-content>
            <?
            if (is_array($docs)) {
                foreach ($docs as $doc) {

                    $docId = $doc["ID"];
                    $docType = $doc["TYPE"];
                    $docStatus = $doc["STATUS"];

                    if ($docType === DOC_TYPE_SIGNED_FILE) {
                        if ($docStatus == DOC_STATUS_BLOCKED){
                            $icon = "lock";
                            $iconCss = "color: red";
                        } else {
                            $icon = "check_circles";
                            $iconCss = "color: rgb(33, 150, 243)";
                        };
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
                <doc-name color="<?= $iconCss ?>" icon="<?= $icon ?>" name="<?= $doc["NAME"] ?>"
                          description="<?= Docs\DocumentsByOrder::getRoleString(Docs\Database::getDocumentById($docId)) ?>">
                </doc-name>
                <doc-buttons>
                    <doc-button icon="email" :id="<?= $docId ?>" @button-click="sendEmail"
                                title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_SEND_DOCS"); ?>">
                    </doc-button>
                    <doc-button icon="create" :id="<?= $docId ?>" @button-click="sign"
                                title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_SIGN"); ?>">
                    </doc-button>
                    <?
                    if ($docType === DOC_TYPE_SIGNED_FILE) {
                    ?>
                    <doc-button icon="info" :id="<?= $docId ?>" @button-click="verify"
                                title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_VERIFY"); ?>">
                    </doc-button>
                    <?
                    }
                    ?>
                    <doc-button icon="save_alt" :id="<?= $docId ?>" @button-click="download"
                                title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_DOWNLOAD"); ?>">
                    </doc-button>
                    <doc-button icon="description" :id="<?= $docId ?>" @button-click="protocol"
                                title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_PROTOCOL"); ?>">
                    </doc-button>
                </doc-buttons>
            </docs-items>
        <?
                }
            }
        ?>
        </docs-content>
    </trca-docs>
</div>

<script>
    new Vue({
        el: '#trca-docs-by-order',
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
            }
        }
    })
</script>

