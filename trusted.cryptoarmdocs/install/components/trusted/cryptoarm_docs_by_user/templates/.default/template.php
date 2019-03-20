<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
?>

<head>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<?
$allIds = $arResult['ALL_IDS'];
$allIdsJs = $arResult['ALL_IDS_JS'];
$docs = $arResult['DOCS'];

$compTitle = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOCS_BY_ORDER") . $USER->GetFullName();
$zipName = $compTitle . " " . date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time());

// DOCUMENT UPLOAD HANDLER - START
$DOCUMENTS_DIR = COption::GetOptionString("trusted.cryptoarmdocs", "DOCUMENTS_DIR", "docs");
if (!empty($_FILES["userfile"]["name"])) {
    $uniqid = (string)uniqid();
    $new_doc_dir = $_SERVER["DOCUMENT_ROOT"] . "/" . $DOCUMENTS_DIR . "/" . $uniqid . "/";
    mkdir($new_doc_dir);
    $new_doc_filename = Docs\Utils::mb_basename($_FILES["userfile"]["name"]);
    $absolute_path = $new_doc_dir . $new_doc_filename;
    $relative_path = "/" . $DOCUMENTS_DIR . "/" . $uniqid . "/" . $new_doc_filename;
    if (move_uploaded_file($_FILES["userfile"]["tmp_name"], $absolute_path)) {
        $props = new Docs\PropertyCollection();
        $props->add(new Docs\Property("USER", (string)$USER->GetID()));
        $doc = Docs\Utils::createDocument($relative_path, $props);

    }
    unset ($_FILES["userfile"]["name"]);
    echo "<script>window.location = window.location.href</script>";
}
// DOCUMENT UPLOAD HANDLER - END
?>

<body>
<div id="main-document">
    <main class="document-card">
        <header class="document-card__title_user">
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
                        <? $emailJs = "promptAndSendEmail($allIdsJs, 'MAIL_EVENT_ID_TO', [], 'MAIL_TEMPLATE_ID_TO')" ?>
                        <div onclick="<?= $emailJs ?>">
                            <div class="material-icons">
                                email
                            </div>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SEND_DOCS_ALL"); ?>
                        </div>
                        <? $signJs = "sign($allIdsJs, {'role': 'CLIENT'})" ?>
                        <div onclick="<?= $signJs ?>">
                            <div class="material-icons">
                                create
                            </div>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SIGN_ALL"); ?>
                        </div>
                        <? $verifyJs = "verify($allIdsJs)" ?>
                        <div onclick="<?= $verifyJs ?>">
                            <div class="material-icons">
                                info
                            </div>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_VERIFY_ALL"); ?>
                        </div>
                        <? $downloadJs = "self.download($allIdsJs, false, '$zipName')" ?>
                        <div onclick="<?= $downloadJs ?>">
                            <div class="material-icons">
                                save_alt
                            </div>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOWNLOAD_ALL"); ?>
                        </div>
                        <?
                        if ($arParams["POSSIBILITY_OF_REMOVAL"] === 'Y') {
                        ?>
                            <? $removeJs = "remove($allIdsJs)" ?>
                            <div onclick="<?= $removeJs ?>">
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
        </header>

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
                                <? $emailJs = "promptAndSendEmail([$docId], 'MAIL_EVENT_ID_TO', [], 'MAIL_TEMPLATE_ID_TO')" ?>
                                <div class="icon-wrapper"
                                     title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SEND_DOCS"); ?>"
                                     onclick="<?= $emailJs ?>">
                                    <i class="material-icons">
                                        email
                                    </i>
                                </div>
                                <?
                                if ($docAccessLevel == "SIGN" || $docAccessLevel == "OWNER") {
                                    $signJs = "sign([$docId], {'role': 'CLIENT'})";
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
                                $verifyJs = "verify([$docId])";
                                ?>
                                <div class="icon-wrapper"
                                     title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_VERIFY"); ?>"
                                     onclick="<?= $verifyJs ?>">
                                    <i class="material-icons">
                                        info
                                    </i>
                                </div>
                                <? $downloadJs = "self.download([$docId], false)" ?>
                                <div class="icon-wrapper"
                                     title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOWNLOAD"); ?>"
                                     onclick="<?= $downloadJs ?>">
                                    <i class="material-icons">
                                        save_alt
                                    </i>
                                </div>
                                <?
                                if ($docAccessLevel == "OWNER") {
                                    $shareJs = "promptAndShare([$docId], 'SHARE_SIGN')"
                                ?>
                                    <div class="icon-wrapper"
                                         title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SHARE"); ?>"
                                         onclick="<?= $shareJs ?>">
                                        <i class="material-icons">
                                            share
                                        </i>
                                    </div>
                                    <?
                                    if ($arParams["POSSIBILITY_OF_REMOVAL"] === 'Y') {
                                        $removeJs = "remove([$docId])";
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
        if ($arParams["POSSIBILITY_OF_ADDING"] === 'Y') {
            if ($USER->IsAuthorized()) {
        ?>
                <footer class="document-card__footer">
                    <form enctype="multipart/form-data" method="POST">
                        <div class="document-footer__action">
                            <input class="document-footer__input" name="userfile" type="file" style="font-size: 0"
                                   onchange=this.form.submit()>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_ADD"); ?>
                        </div>
                    </form>
                </footer>
        <?
            }
        }
        ?>
    </main>
</div>
</body>

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

