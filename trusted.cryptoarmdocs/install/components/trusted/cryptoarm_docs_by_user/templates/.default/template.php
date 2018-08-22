<?php

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;

?>

<head>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<? $all_ids = array();
while ($docsList = $arResult->Fetch()) {
    $docs_info[] = array(
        "ID" => $docsList["ID"],
        "NAME" => $docsList["NAME"],
        "STATUS" => $docsList["STATUS"],
    );
    $all_ids[] = $docsList["ID"];
}

$title = Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOCS_BY_ORDER") . $USER->GetFullName();
$zipName = $title . " " . date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time());

$DOCUMENTS_DIR = COption::GetOptionString("trusted.cryptoarmdocs", "DOCUMENTS_DIR", "docs");
if (!empty($_FILES["userfile"]["name"])) {
    {
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
        header("Refresh:0");
    }
} ?>

<body>
<div id="main-document">
    <main class="document-card">
        <header class="document-card__title_user">
            <?= $title ?>
            <? if (!empty($all_ids)) { ?>
                <div id="sweeties" class="menu">
                    <div class="icon-wrapper">
                        <div class="material-icons title">
                            more_vert
                        </div>
                    </div>
                    <ul id="ul_by_user">
                        <div onclick="sign(<?= json_encode($all_ids) ?>, {'role': 'CLIENT'} )">
                            <div class="material-icons">
                                create
                            </div>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SIGN_ALL"); ?>
                        </div>
                        <div onclick="verify(<?= json_encode($all_ids) ?>)">
                            <div class="material-icons">
                                info
                            </div>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_VERIFY_ALL"); ?>
                        </div>
                        <div onclick="self.download(<?= json_encode($all_ids) ?>, true, '<?= $zipName ?>')">
                            <div class="material-icons">
                                save_alt
                            </div>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOWNLOAD_ALL"); ?>
                        </div>
                        <div onclick="remove(<?= json_encode($all_ids) ?>)">
                            <div class="material-icons">
                                delete
                            </div>
                            <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DELETE_ALL"); ?>
                        </div>
                    </ul>
                </div>
            <? } ?>
        </header>

        <div class="document-card__content">
            <? foreach ($docs_info as $doc) { ?>
                <div class="document-content__item">
                    <div class="document-item__left">
                        <?
                        $doc_id = $doc["ID"];
                        $doc_name = $doc["NAME"];
                        $doc_status = $doc["STATUS"];
                        $doc_info = Docs\Database::getDocumentById($doc_id);
                        $doc_sign_type = $doc_info->getType();
                        $doc_sign_status = NULL;
                        $doc_sign_status = $doc_info->getStatus();
                        if ($doc_sign_type == DOC_TYPE_SIGNED_FILE) { ?>
                            <div class="material-icons" style="color: rgb(33, 150, 243);">
                                check_circles
                            </div>
                        <? } else {
                            switch ($doc_sign_status) {
                                case DOC_STATUS_NONE:
                                    { ?>
                                        <div class="material-icons" style="color: green">
                                            insert_drive_file
                                        </div>
                                        <? break;
                                    }
                                case DOC_STATUS_BLOCKED:
                                    { ?>
                                        <div class="material-icons" style="color: red">
                                            lock
                                        </div>
                                        <? break;
                                    }
                                case DOC_STATUS_CANCELED:
                                    { ?>
                                        <div class="material-icons" style="color: red">
                                            insert_drive_file
                                        </div>
                                        <? break;
                                    }
                                case DOC_STATUS_ERROR:
                                    { ?>
                                        <div class="material-icons" style="color: red">
                                            error
                                        </div>
                                        <? break;
                                    }
                            }
                        } ?>
                        <div class="document-item__text">
                            <div class="document-text__title">
                                <?= $doc_name ?>
                            </div>
                            <div class="document-text__description">
                                <? if ($doc_sign_status === DOC_STATUS_BLOCKED) {
                                    echo Docs\Utils::getStatusString($doc_info);
                                } else {
                                    echo Docs\Utils::getTypeString($doc_info);
                                } ?>
                            </div>
                        </div>
                    </div>
                    <div class="document-item__right_user">
                        <div class="icon-wrapper" title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_SIGN"); ?>"
                             onclick="window.parent.sign([<?= $doc_id ?>], {'role': 'CLIENT'})">
                            <i class="material-icons">
                                create
                            </i>
                        </div>
                        <div class="icon-wrapper"
                             title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_VERIFY"); ?>"
                             onclick="verify([<?= $doc_id ?>])">
                            <i class="material-icons">
                                info
                            </i>
                        </div>
                        <div class="icon-wrapper"
                             title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DOWNLOAD"); ?>"
                             onclick="self.download([<?= $doc_id ?>], true)">
                            <i class="material-icons">
                                save_alt
                            </i>
                        </div>
                        <div class="icon-wrapper"
                             title="<?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_DELETE"); ?>"
                             onclick="window.parent.remove([<?= $doc_id ?>])">
                            <i class="material-icons">
                                delete
                            </i>
                        </div>
                    </div>
                </div>
            <? } ?>
        </div>
        <? if ($USER->IsAuthorized()) { ?>
            <footer class="document-card__footer">
                <form enctype="multipart/form-data" method="POST">
                    <div class="document-footer__action">
                        <input class="document-footer__input" name="userfile" type="file" style="font-size: 0"
                               onchange=this.form.submit()>
                        <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_USER_ADD"); ?>
                    </div>
                </form>

            </footer>
        <? } ?>
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
