<?php

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;

?>

    <div class="col-md-12 col-sm-12 col-xs-12 sale-order-detail-payment-options-order-content-title docs">
        <h3><?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_NUMBER"); ?><?= $arParams["ORDER"] ?></h3>
    </div>
    <table>
        <thead>
        <tr>
            <td><?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_DOCS"); ?></td>
            <td><?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_STATUS"); ?></td>
            <td></td>
        </tr>
        </thead>
        <? while ($doc = $arResult->Fetch()) { ?>
        <tbody>
        <tr>
            <td><?= $doc['NAME'] ?></td>
            <td> <?= Docs\DocumentsByOrder::getRoleString(Docs\Database::getDocumentById($doc["ID"])) ?></td>
            <?
            if ($doc["STATUS"] === DOC_STATUS_NONE) { ?>
                <td><a href="#" class="button" onclick="window.parent.sign([<?= $doc["ID"] ?>], {role: 'CLIENT'})">
                        <?= Loc::getMessage("TR_CA_DOCS_COMP_DOCS_BY_ORDER_SIGN"); ?>
                    </a>
                </td>
            <? } ?>
        </tr>
        <? } ?>
        </tbody>
    </table>
<?
/*$pag = $arResult->GetPageNavStringEx($navComponentObject, "", 'arrows');
echo $pag;*/
