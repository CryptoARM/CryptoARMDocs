<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;

Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">');
Asset::getInstance()->addString('<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">');

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$allIds = $arResult['ALL_IDS'];
$allIdsJs = $arResult['ALL_IDS_JS'];
$docs = $arResult['DOCS'];

$compTitle = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOCS_BY_ORDER") . $USER->GetFullName();
$zipName = $compTitle . " " . date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time());
?>

<a id="reload_doc_by_user_comp" href="<?= $_SERVER["REQUEST_URI"] ?>"></a>

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

<div id="main-document">
    <main class="document-card">
        <div class="document-card__title_user">
            <?
            echo $compTitle;
            if (!empty($allIds)) {
            ?>
                <div id="sweeties" class="menu">
                    <div class="icon-wrapper">
                        <div class="material-icons title">
                            more_vert
                        </div>
                    </div>
                    <ul id="ul_by_user">
                        <? $emailAllJs = "trustedCA.promptAndSendEmail($allIdsJs, 'MAIL_EVENT_ID_TO', {}, 'MAIL_TEMPLATE_ID_TO')" ?>
                        <div onclick="<?= $emailAllJs ?>">
                            <div class="material-icons">
                                email
                            </div>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SEND_DOCS_ALL"); ?>
                        </div>
                        <? $signAllJs = "trustedCA.sign($allIdsJs, {'role': 'CLIENT'}, reloadDocByUserComp)" ?>
                        <div onclick="<?= $signAllJs ?>">
                            <div class="material-icons">
                                create
                            </div>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SIGN_ALL"); ?>
                        </div>
                        <? $verifyAllJs = "trustedCA.verify($allIdsJs)" ?>
                        <div onclick="<?= $verifyAllJs ?>">
                            <div class="material-icons">
                                info
                            </div>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_VERIFY_ALL"); ?>
                        </div>
                        <? $downloadAllJs = "trustedCA.download($allIdsJs, '$zipName')" ?>
                        <div onclick="<?= $downloadAllJs ?>">
                            <div class="material-icons">
                                save_alt
                            </div>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOWNLOAD_ALL"); ?>
                        </div>
                        <?
                        if ($arParams["ALLOW_REMOVAL"] === 'Y') {
                        ?>
                            <? $removeAllJs = "trustedCA.remove($allIdsJs, false, reloadDocByUserComp)" ?>
                            <div onclick="<?= $removeAllJs ?>">
                                <div class="material-icons">
                                    delete
                                </div>
                                <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DELETE_ALL"); ?>
                            </div>
                        <?
                        }
                        ?>
                    </ul>
                </div>
            <?
            }
            ?>
        </div>

        <div class="document-card__content">
            <?
            if (is_array($docs)) {
                foreach ($docs as $doc) {
                    $docId = $doc["ID"];
                    $docType = $doc["TYPE"];
                    $docStatus = $doc["STATUS"];
                    $docAccessLevel = $doc["ACCESS_LEVEL"];

                    if ($docType == DOC_TYPE_SIGNED_FILE) {
                        $icon = "check_circles";
                        $iconCss = "color: rgb(33, 150, 243)";
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
                    <div class="document-content__item">
                        <div class="document-item__left">
                            <div class="material-icons" style="<?= $iconCss ?>">
                                <?= $icon ?>
                            </div>
                            <div class="document-item__text">
                                <div class="document-text__title">
                                    <?= $doc["NAME"] ?>
                                </div>
                                <div class="document-text__description">
                                    <?
                                    if ($docStatus === DOC_STATUS_BLOCKED) {
                                        echo $doc['STATUS_STRING'];
                                    } else {
                                        echo $doc['TYPE_STRING'];
                                    }
                                    ?>
                                </div>
                                <div class="document-text__share">
                                    <?
                                    echo Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_OWNER");
                                    if ($docAccessLevel == "OWNER") {
                                        echo Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_OWNER2");
                                    } else {
                                        echo $doc['OWNER_USERNAME'];
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="document-item__right_user">
                            <div class="icon_content">
                                <? $emailJs = "trustedCA.promptAndSendEmail([$docId], 'MAIL_EVENT_ID_TO', {}, 'MAIL_TEMPLATE_ID_TO')" ?>
                                <div class="icon-wrapper"
                                     title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SEND_DOCS"); ?>"
                                     onclick="<?= $emailJs ?>">
                                    <i class="material-icons">
                                        email
                                    </i>
                                </div>
                                <?
                                if ($docAccessLevel == "SIGN" || $docAccessLevel == "OWNER") {
                                    $signJs = "trustedCA.sign([$docId], {'role': 'CLIENT'}, reloadDocByUserComp)";
                                ?>
                                    <div class="icon-wrapper"
                                         title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SIGN"); ?>"
                                         onclick="<?= $signJs ?>">
                                        <i class="material-icons">
                                            create
                                        </i>
                                    </div>
                                <?
                                }
                                $verifyJs = "trustedCA.verify([$docId])";
                                if ($docType === DOC_TYPE_SIGNED_FILE) {
                                ?>
                                    <div class="icon-wrapper"
                                        title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_VERIFY"); ?>"
                                        onclick="<?= $verifyJs ?>">
                                        <i class="material-icons">
                                            info
                                        </i>
                                    </div>
                                <?
                                }
                                ?>
                                <? $downloadJs = "trustedCA.download([$docId])" ?>
                                <div class="icon-wrapper"
                                     title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOWNLOAD"); ?>"
                                     onclick="<?= $downloadJs ?>">
                                    <i class="material-icons">
                                        save_alt
                                    </i>
                                </div>
                                <? $protocolJs = "trustedCA.protocol($docId)" ?>
                                <div class="icon-wrapper"
                                     title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_PROTOCOL"); ?>"
                                     onclick="<?= $protocolJs ?>">
                                    <i class="material-icons">
                                        description
                                    </i>
                                </div>
                                <?
                                if ($docAccessLevel == "OWNER") {
                                    $shareJs = "trustedCA.promptAndShare([$docId], 'SHARE_SIGN')"
                                ?>
                                    <div class="icon-wrapper"
                                         title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SHARE"); ?>"
                                         onclick="<?= $shareJs ?>">
                                        <i class="material-icons">
                                            share
                                        </i>
                                    </div>
                                    <?
                                    if ($arParams["ALLOW_REMOVAL"] === 'Y') {
                                        $removeJs = "trustedCA.remove([$docId], false, reloadDocByUserComp)";
                                    ?>
                                        <div class="icon-wrapper"
                                             title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DELETE"); ?>"
                                             onclick="<?= $removeJs ?>">
                                            <i class="material-icons">
                                                delete
                                            </i>
                                        </div>
                                <?
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
            <?
                }
            }
            ?>
        </div>
        <?
        if ($arParams["ALLOW_ADDING"] === 'Y') {
            if ($USER->IsAuthorized()) {
            $maxSize  = Docs\Utils::maxUploadFileSize();
            $sizeFileJS = "trustedCA.checkFileSize(this.files[0], $maxSize, () => { $('#document-footer__download').submit() }, () => { $('#document-footer__input').val(null) })";
        ?>
                <div class="document-card__footer">
                    <form enctype="multipart/form-data" method="POST" id="document-footer__download">
                        <div class="document-footer__action">
                            <input id="document-footer__input" class="document-footer__input" name="tr_ca_upload_comp_by_user" type="file" style="font-size: 0"
                                    onchange="<?= $sizeFileJS ?>">
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_ADD"); ?>
                        </div>
                    </form>
                </div>
        <?
            }
        }
        ?>
    </main>
</div>

<script>
    $(".document-card__title_user").click(function () {
        $('#ul_by_user').toggle();
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest(".title").length) {
            $('#ul_by_user').hide();
        }
        e.stopPropagation();
    });
</script>

