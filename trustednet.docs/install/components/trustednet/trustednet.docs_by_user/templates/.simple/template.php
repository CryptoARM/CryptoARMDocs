<?php
use TrustedNet\Docs;
use Bitrix\Main\Localization\Loc;
?>

<? if ($USER->IsAuthorized()) { ?>
    <table>
        <td><?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_DOC_ID"); ?></td>
        <td><?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_DOC_NAME"); ?></td>
        <td><?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_DOC_STATUS"); ?></td>
        <td></td>
        <?
        $i = 0;
        while ($doc = $arResult->NavNext()) { ?>
            <tr>
                <td> <?= $doc["ID"] ?> </td>
                <td> <?= $doc["NAME"] ?> </td>
                <td> <?= Docs\DocumentsByOrder::getRoleString(Docs\Database::getDocumentById($doc["ID"])) ?></td>
                <?
                $i++;
                if ($doc["STATUS"] === DOC_STATUS_NONE) {
                    ?>
                    <td><a href="#" class="button15"
                           onclick="window.parent.sign([<?= $doc["ID"] ?>], {role: 'CLIENT'})"><?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_DOC_SIGN"); ?></a>
                    </td>
                    <?
                }
                ?>
            </tr>
            <?
        }
        ?>
        <? if ($i == 0) { ?>
            <tr>
                <td colspan="4" align="center">
                    <not_exist>
                        <?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_DOC_DOES_NOT_EXIST"); ?>
                    </not_exist>
                </td>
            </tr>
            <?
        }
        ?>
    </table>
    <?
} else {
    ?>
    <not_exist>
        <?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_USER_NOT_LOGIN");
        ?>
    </not_exist>
    <?
}
?>
    <br>
<?
/*$pag = $arResult->GetPageNavStringEx($navComponentObject, "", 'modern');
echo $pag;*/
