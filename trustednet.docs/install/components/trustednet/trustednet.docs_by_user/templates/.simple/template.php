<?php

use TrustedNet\Docs;
use Bitrix\Main\Localization\Loc;

?>

    <table>
        <td><?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_DOC_ID"); ?></td>
        <td><?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_DOC_NAME"); ?></td>
        <td><?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_DOC_STATUS"); ?></td>
        <td></td>
        <? while ($doc = $arResult->Fetch()) { ?>
            <tr>
                <td> <?= $doc["ID"] ?> </td>
                <td> <?= $doc["NAME"] ?> </td>
                <td> <?= Docs\DocumentsByOrder::getRoleString(Docs\Database::getDocumentById($doc["ID"])) ?></td>
                <? if ($doc["STATUS"] === DOC_STATUS_NONE) { ?>
                    <td><a href="#" class="button"
                           onclick="window.parent.sign([<?= $doc["ID"] ?>], {role: 'CLIENT'})"><?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_DOC_SIGN"); ?></a>
                    </td>
                <? } ?>
            </tr>
        <? } ?>
    </table>
    <br>
<?
/*$pag = $arResult->GetPageNavStringEx($navComponentObject, "", 'modern');
echo $pag;*/
