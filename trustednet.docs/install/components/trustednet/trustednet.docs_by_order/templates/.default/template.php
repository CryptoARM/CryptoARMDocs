<?php

use TrustedNet\Docs;
use Bitrix\Main\Localization\Loc;

?>

<head>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<?
$all_ids = array();
while ($docsList = $arResult->Fetch()) {
    $docs_info[] = array(
        "ID" => $docsList["ID"],
        "NAME" => $docsList["NAME"],
        "STATUS" => $docsList["STATUS"],
    );
    $all_ids[] = $docsList["ID"];
}
?>

<body>
<div id="main-document">
    <main class="document-card">
        <header class="document-card__title">
            <?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_ORDER_DOCS_BY_ORDER"); ?><?= $arParams["ORDER"] ?>
            <div id="sweeties" class="menu">
                <div class="icon-wrapper">
                    <div class="material-icons title">
                        more_vert
                    </div>
                </div>
                <ul id="ul_by_order">
                    <div onclick="sign(<?= json_encode($all_ids) ?>, {'role': 'CLIENT'} ) || closed()">
                        <div class="material-icons">
                            create
                        </div>
                        <?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_ORDER_SIGN_ALL"); ?>
                    </div>
                    <div onclick="verify(<?= json_encode($all_ids) ?>) || closed()">
                        <div class="material-icons">
                            info
                        </div>
                        <?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_ORDER_VERIFY_ALL"); ?>
                    </div>
                    <div onclick="download_all_by_order(<?= json_encode($all_ids) ?>) || closed()">
                        <div class="material-icons">
                            save_alt
                        </div>
                        <?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_ORDER_DOWNLOAD_ALL"); ?>
                    </div>
                </ul>
            </div>
        </header>

        <div class="document-card__content">
            <?
            foreach ($docs_info as $doc) {
                ?>
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
                        if ($doc_sign_type === DOC_TYPE_SIGNED_FILE) { ?>
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
                                <?= Docs\DocumentsByOrder::getRoleString(Docs\Database::getDocumentById($doc_id)) ?>
                            </div>
                        </div>
                    </div>
                    <div class="document-item__right">
                        <div class="icon-wrapper" title="<?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_ORDER_SIGN"); ?>"
                             onclick="window.parent.sign([<?= $doc_id ?>], {'role': 'CLIENT'} )">
                            <i class="material-icons">
                                create
                            </i>
                        </div>
                        <div class="icon-wrapper" title="<?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_ORDER_VERIFY"); ?>"
                             onclick="verify([<?= $doc_id ?>])">
                            <i class="material-icons">
                                info
                            </i>
                        </div>
                        <div class="icon-wrapper" title="<?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_ORDER_DOWNLOAD"); ?>"
                             onclick="self.download(<?= $doc_id ?>, true)">
                            <i class="material-icons">
                                save_alt
                            </i>
                        </div>
                    </div>
                </div>
            <? } ?>
        </div>
    </main>
</div>
</body>

<script>
    function download_all_by_order(ids) {
        var i = 0;
        ids.forEach(function (id) {
            window.setTimeout(self.download, i, id);
            i += 200;
        });
    }

    $(".document-card").click(function () {
        $('#ul_by_order').toggle();
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest(".title").length) {
            $('#ul_by_order').hide();
        }
        e.stopPropagation();
    });
</script>
