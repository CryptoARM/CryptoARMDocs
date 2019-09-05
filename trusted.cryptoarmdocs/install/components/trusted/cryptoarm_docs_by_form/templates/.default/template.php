<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

?>
<form id="crypto-arm-document__by-form" method="POST">
    <div id="main-document">
        <main class="document-card">
            <div class="document-card__title_form">
                <?=
                Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_TITLE")
                ?>
            </div>

            <div class="document-card__content">
                <?
                foreach ($arResult["FORMS"] as $form) {
                    $formId = $form["ID"];
                    $docs = Docs\Database::getDocumentsByPropertyTypeAndValue("FORM", $formId);
                    $docList = $docs->getList();
                    $docIds = [];
                    foreach ($docList as $doc) {
                        $docsType = $doc->getType();
                        $docsStatus = $doc->getStatus();
                        $docIds[] = $doc->getId();
                    }

                    $docIds = json_encode($docIds);
                    $mainDocId = $form["NAME"];

                    $zipName = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_ZIP_FILE_NAME") . $form["DATE_CREATE"];

                    if ($docType == DOC_TYPE_SIGNED_FILE) {
                        if ($docStatus == DOC_STATUS_BLOCKED) {
                            $icon = "lock";
                            $iconCss = "color: red";
                            $status = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_STATUS_BLOCKED");
                        } else {
                            $icon = "check_circles";
                            $iconCss = "color: rgb(33, 150, 243)";
                            $status = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_STATUS_SIGN");
                        }
                    } else {
                        switch ($docStatus) {
                            case DOC_STATUS_NONE:
                                $icon = "insert_drive_file";
                                $iconCss = "color: green";
                                $status = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_STATUS_DRAFT");
                                break;
                            case DOC_STATUS_BLOCKED:
                                $icon = "lock";
                                $iconCss = "color: red";
                                $status = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_STATUS_DRAFT");
                                break;
                            case DOC_STATUS_CANCELED:
                                $icon = "insert_drive_file";
                                $iconCss = "color: red";
                                $status = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_STATUS_DRAFT");
                                break;
                            case DOC_STATUS_ERROR:
                                $icon = "error";
                                $iconCss = "color: red";
                                $status = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_STATUS_DRAFT");
                                break;
                        }
                    }
                    ?>
                    <div class="document-content__item">
                        <div class="document-item__left_form">
                            <div class="material-icons" style="<?= $iconCss ?>">
                                <?=
                                $icon
                                ?>
                            </div>
                            <div class="date_create">
                                <?=
                                $form["DATE_CREATE"]
                                ?>
                            </div>
                            <div class="iblock_name">
                                <?=
                                $form["IBLOCK_NAME"]
                                ?>
                            </div>
                        </div>
                        <div class="document-item__right_form">
                            <div class="icon_content">
                                <?
                                $viewJs = "trustedCA.download([$mainDocId], true)";
                                ?>
                                <a href="<?= Docs\Form::getFirstDocument($mainDocId) ?>" target="_blank">
                                    <div class="icon-wrapper"
                                         title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_VIEW"); ?>"
                                         >
                                        <i class="material-icons">
                                            pageview
                                        </i>
                                    </div>
                                </a>

                                <?
                                $downloadJs = "trustedCA.content($docIds, '$zipName')"
                                ?>
                                <div class="icon-wrapper"
                                     title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_DOWNLOAD"); ?>"
                                     onclick="<?= $downloadJs ?>">
                                    <i class="material-icons">
                                        save_alt
                                    </i>
                                </div>
                                <?
                                if ($arResult["PERMISSION_REMOVE"]) {
                                    $removeJs = "trustedCA.removeForm([$formId], trustedCA.reloadDoc)";
                                    ?>
                                    <div class="icon-wrapper"
                                         title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_FORM_DELETE"); ?>"
                                         onclick="<?= $removeJs ?>">
                                        <i class="material-icons">
                                            delete
                                        </i>
                                    </div>
                                    <?
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <?
                }
                ?>
            </div>
        </main>
    </div>
</form>
