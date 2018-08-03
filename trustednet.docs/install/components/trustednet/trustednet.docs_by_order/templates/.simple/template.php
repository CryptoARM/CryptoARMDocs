<?php

use TrustedNet\Docs;
use Bitrix\Main\Localization\Loc;

?>


    <div class="col-md-12 col-sm-12 col-xs-12 sale-order-detail-payment-options-order-content-title docs">
            <h3>Документы в заказе</h3>
    </div>


                <table>
                    <thead>
                    <tr>
                            <td><?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_ORDER_DOCS"); ?></td>
                            <td><?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_ORDER_STATUS"); ?></td>
                            <td></td>
                    </tr>
                    </thead>
                    <?
                    $i = 0;
                    while ($doc = $arResult->Fetch()) { ?>
                    <tbody>
                    <tr>
                        <td><?= $doc['NAME'] ?> </td>
                        <td> <?= Docs\DocumentsByOrder::getRoleString(Docs\Database::getDocumentById($doc["ID"])) ?></td>
                        <?
                        $i++;
                        if ($doc["STATUS"] === DOC_STATUS_NONE) {
                            ?>
                            <td><a href="#" class="button"
                                   onclick="window.parent.sign([<?= $doc["ID"] ?>], {role: 'CLIENT'})"><?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_ORDER_SIGN"); ?></a>
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
                        <td colspan="3" align="center">
                            <not_exist>
                                <?= Loc::getMessage("TN_DOCS_COMP_DOCS_BY_ORDER_DOCS_DOES_NOT_EXIST"); ?>
                            </not_exist>
                        </td>
                    </tr>
                    </tbody>
                <?
                }
                ?>
                </table>

                <br>





<?
/*$pag = $arResult->GetPageNavStringEx($navComponentObject, "", 'arrows');
echo $pag;*/
