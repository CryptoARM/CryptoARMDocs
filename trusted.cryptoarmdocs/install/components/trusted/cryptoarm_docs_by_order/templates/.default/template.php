<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$allIds = $arResult['ALL_IDS'];
$allIdsJs = $arResult['ALL_IDS_JS'];
$docs = $arResult['DOCS'];

$title = Loc::getMessage('TR_CA_DOCS_COMP_DOCS_BY_ORDER_DOCS_BY_ORDER') . $arParams['ORDER'];
$zipName = $title . ' ' . date($DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), time());
?>

<a id="trca-reload-doc" href="<?= $_SERVER['REQUEST_URI'] ?>"></a>

<div id="main-document">
    <main class="document-card">
        <div class="document-card__title">
            <?= $title ?>
            <?php if (!empty($allIds)) { ?>
                <div id="sweeties" class="menu">
                    <div class="icon-wrapper">
                        <div class="material-icons title">
                            more_vert
                        </div>
                    </div>
                    <ul id="ul_by_order">
                        <?php $emailAllJs = "trustedCA.promptAndSendEmail($allIdsJs, 'MAIL_EVENT_ID_TO', {}, 'MAIL_TEMPLATE_ID_TO')"; ?>
                        <div onclick="<?= $emailAllJs ?>">
                            <div class="material-icons">
                                email
                            </div>
                            <?= Loc::getMessage('TR_CA_DOCS_COMP_DOCS_BY_ORDER_SEND_DOCS_ALL') ?>
                        </div>
                        <?php $signAllJs = "trustedCA.sign($allIdsJs, {'role': 'CLIENT'})"; ?>
                        <div onclick="<?= $signAllJs ?>">
                            <div class="material-icons">
                                create
                            </div>
                            <?= Loc::getMessage('TR_CA_DOCS_COMP_DOCS_BY_ORDER_SIGN_ALL') ?>
                        </div>
                        <?php $verifyAllJs = "trustedCA.verify($allIdsJs)"; ?>
                        <div onclick="<?= $verifyAllJs ?>">
                            <div class="material-icons">
                                info
                            </div>
                            <?= Loc::getMessage('TR_CA_DOCS_COMP_DOCS_BY_ORDER_VERIFY_ALL') ?>
                        </div>
                        <?php $downloadAllJs = "trustedCA.download($allIdsJs, '$zipName')"; ?>
                        <div onclick="<?= $downloadAllJs ?>">
                            <div class="material-icons">
                                save_alt
                            </div>
                            <?= Loc::getMessage('TR_CA_DOCS_COMP_DOCS_BY_ORDER_DOWNLOAD_ALL') ?>
                        </div>
                    </ul>
                </div>
            <?php } ?>
        </div>

        <div class="document-card__content">
            <?php if (is_array($docs)) {
                foreach ($docs as $doc) {

                    $docId = $doc['ID'];
                    $docType = $doc['TYPE'];
                    $docStatus = $doc['STATUS'];

                    if ($docType === DOC_TYPE_SIGNED_FILE) {
                        if ($docStatus == DOC_STATUS_BLOCKED) {
                            $icon = 'lock';
                            $iconCss = 'color: red';
                        } else {
                            $icon = 'check_circles';
                            $iconCss = 'color: rgb(33, 150, 243)';
                        }
                    } else {
                        switch ($docStatus) {
                            case DOC_STATUS_NONE:
                                $icon = 'insert_drive_file';
                                $iconCss = 'color: green';
                                break;
                            case DOC_STATUS_BLOCKED:
                                $icon = 'lock';
                                $iconCss = 'color: red';
                                break;
                            case DOC_STATUS_CANCELED:
                                $icon = 'insert_drive_file';
                                $iconCss = 'color: red';
                                break;
                            case DOC_STATUS_ERROR:
                                $icon = 'error';
                                $iconCss = 'color: red';
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
                            <?= $doc['NAME'] ?>
                        </div>
                        <div class="document-text__description">
                            <?= Docs\DocumentsByOrder::getRoleString(
                                Docs\Database::getDocumentById($docId)
                            ) ?>
                        </div>
                    </div>
                </div>
                <div class="document-item__right">
                    <?php $emailJs = "trustedCA.promptAndSendEmail([$docId], 'MAIL_EVENT_ID_TO', {}, 'MAIL_TEMPLATE_ID_TO')"; ?>
                    <div class="icon-wrapper"
                         title="<?= Loc::getMessage('TR_CA_DOCS_COMP_DOCS_BY_ORDER_SEND_DOCS') ?>"
                         onclick="<?= $emailJs ?>">
                        <i class="material-icons">
                            email
                        </i>
                    </div>
                    <?php $signJs = "trustedCA.sign([$docId], {'role': 'CLIENT'})"; ?>
                    <div class="icon-wrapper"
                         title="<?= Loc::getMessage('TR_CA_DOCS_COMP_DOCS_BY_ORDER_SIGN') ?>"
                         onclick="<?= $signJs ?>">
                        <i class="material-icons">
                            create
                        </i>
                    </div>
                    <?php
                    $verifyJs = "trustedCA.verify([$docId])";
                    if ($docType === DOC_TYPE_SIGNED_FILE) { ?>
                        <div class="icon-wrapper"
                            title="<?= Loc::getMessage('TR_CA_DOCS_COMP_DOCS_BY_ORDER_VERIFY') ?>"
                            onclick="<?= $verifyJs ?>">
                            <i class="material-icons">
                                info
                            </i>
                        </div>
                    <?php }
                    ?>
                    <?php $downloadJs = "trustedCA.download([$docId], true)"; ?>
                    <div class="icon-wrapper"
                         title="<?= Loc::getMessage('TR_CA_DOCS_COMP_DOCS_BY_ORDER_DOWNLOAD') ?>"
                         onclick="<?= $downloadJs ?>">
                        <i class="material-icons">
                            save_alt
                        </i>
                    </div>
                    <?php $protocolJs = "trustedCA.protocol($docId)"; ?>
                    <div class="icon-wrapper"
                         title="<?= Loc::getMessage('TR_CA_DOCS_COMP_DOCS_BY_ORDER_PROTOCOL') ?>"
                         onclick="<?= $protocolJs ?>">
                        <i class="material-icons">
                            description
                        </i>
                    </div>
                </div>
            </div>
        <?php
                }
            } ?>
        </div>
    </main>
</div>

<script>
    $(".document-card__title").click(function () {
        $('#ul_by_order').toggle();
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest(".title").length) {
            $('#ul_by_order').hide();
        }
        e.stopPropagation();
    });
</script>

