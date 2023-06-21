<?php

$module_id = "trusted.cryptoarmdocsfree";

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $module_id . "/config.php";

$MESS["TR_CA_DOCS_MODULE_NAME"] = "КриптоАРМ Документы";
$MESS["TR_CA_DOCS_MODULE_DESCRIPTION"] = "Модуль работы с документами";
$MESS["TR_CA_DOCS_PARTNER_NAME"] = 'ООО "Цифровые технологии"';
$MESS["TR_CA_DOCS_PARTNER_URI"] = "https://trusted.ru";

$MESS["TR_CA_DOCS_CRM_MENU_TITLE"] = "КриптоАРМ Документы";

// email by order
$MESS["TR_CA_DOCS_MAIL_EVENT_NAME"] = "КриптоАРМ Документы - рассылка документов по заказам";
$MESS["TR_CA_DOCS_MAIL_EVENT_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#ORDER_USER# - имя пользователя, совершившего заказ
#ORDER_ID# - номер заказа
#FILE_NAMES# - список названий документов
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SUBJECT"] = "#SITE_NAME#: Документы по заказу №#ORDER_ID#";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_BODY"] = "
Документы: #FILE_NAMES# по заказу №#ORDER_ID# для #ORDER_USER#.
<img src=\"#SITE_URL#/bitrix/components/trusted/docs/email.php?order_id=#ORDER_ID#&rand=#RAND_UID#\" alt=\"\">
";

// email documents
$MESS["TR_CA_DOCS_MAIL_EVENT_TO_NAME"] = "КриптоАРМ Документы - отправка документов";
$MESS["TR_CA_DOCS_MAIL_EVENT_TO_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAMES# - список названий документов
#FIO_FROM# - от кого сообщение
#FIO_TO# - кому сообщение
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_TO_SUBJECT"] = "Вам отправлен документ (#FILE_NAMES#)";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_TO_BODY"] = '
<table cellspacing="0" style="padding: 16px; border:1px solid #c8c8c8; border-radius:4px">
    <tbody>
    <tr>
        <td>
            <table class="body" data-made-with-foundation="">
                <tbody>
                    <tr>
                        <td rowspan="2" style="padding-right: 8px">
                            <img src="' . TR_CA_DOCS_PATH_TO_POST_ICONS . 'favicon.ico" class="img" style="border-width: 0; width: 40px; height: 40px;" alt="">
                        </td>
                        <td>
                            <p style="margin: 0; font-size: 20px;">
                                <span style="color:rgba(51, 51, 51, 0.866666666666667);">Вам отправлен документ&nbsp;
                                (</span> <span style="color:rgba(217, 0, 27, 0.866666666666667);">#FILE_NAMES#</span>&nbsp;
                                <span style="color:rgba(51, 51, 51, 0.866666666666667);">)</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="margin: 0; color: rgba(0, 0, 0, 0.6); line-height: 20px;">
                                КриптоАРМ Документы
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="body" data-made-with-foundation="">
                <tbody>
                    <tr style="height: 36px">
                        <td>
                            <p style="margin: 0;">
                                Здравствуйте, <span style="color:#D9001B;">#FIO_TO#</span> !
                            </p>
                        </td>
                    </tr>
                    <tr style="height: 36px">
                        <td>
                            <p style="margin: 0;">
                                <span style="color:#D9001B;">#FIO_FROM#</span> отправил вам документ " <span style="color:#D9001B;">#FILE_NAMES#</span> ".
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<br>';

// email about share documents
$MESS["TR_CA_DOCS_MAIL_EVENT_SHARE_NAME"] = "КриптоАРМ Документы - уведомление о получении доступа к документу";
$MESS["TR_CA_DOCS_MAIL_EVENT_SHARE_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAME# - название документа
#SHARE_FROM# - автор документа
#FIO_TO# - кому сообщение
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SHARE_SUBJECT"] = "Вам открыт доступ к документу (#FILE_NAME#)";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_SHARE_BODY"] = '
<table cellspacing=0 style="padding: 16px; border:1px solid #c8c8c8; border-radius:4px">
    <tbody>
    <tr>
        <td>
        <table class="body" data-made-with-foundation>
            <tbody>
            <tr>
            <td rowspan="2">
                <img class="img" src="' . TR_CA_DOCS_PATH_TO_POST_ICONS . 'favicon.ico" style="border-width: 0; width: 40px; height: 40px; padding-right: 8px;" alt="">
            </td>
            <td>
                <p style="margin: 0; font-size: 20px;">
                    <span style="color:rgba(51, 51, 51, 0.866666666666667);">Вам открыт доступ к документу (</span>
                    <span style="color:rgba(217, 0, 27, 0.866666666666667);">#FILE_NAMES#</span>
                    <span style="color:rgba(51, 51, 51, 0.866666666666667);">)</span>
                </p>
            </td>
            </tr>
            <tr>
                <td>
                <p style="margin: 0; color: rgba(0, 0, 0, 0.6); line-height: 20px;">
                    <span>КриптоАРМ Документы</span>
                </p>
                </td>
            </tr>
        </tbody>
        </table>
        <table class="body" data-made-with-foundation>
        <tbody>
            <tr style="height: 36px">
            <td>
                <p style="margin: 0;">
                    <span>Здравствуйте,</span>
                    <span style="color:#D9001B;">#FIO_TO#</span>!
                </p>
            </td>
            </tr>

            <tr style="height: 36px">
            <td>
                <p style="margin: 0;">
                    <span style="color:#D9001B;">#SHARE_FROM#</span>
                    <span>предоставил вам доступ к документу "</span>
                    <span style="color:#D9001B;">#FILE_NAMES#</span>
                    <span>".</span>
                </p>
            </td>
            </tr>

            <tr style="height: 36px">
            <td>
                <p style="margin: 0;">
                    <span>Ознакомиться с ним вы можете, пройдя по ссылке в свой личный кабинет портала </span>
                    <span>
                        <a target="_blank" href="https://' . TR_CA_HOST . '" style="text-decoration: none; color:#D9001B; cursor: pointer;">
                            https://' . TR_CA_HOST . '
                        </a>
                    </span>
                </p>
            </td>
            </tr>

            <tr style="height: 72px">
            <td>
                <p>
                    <span>
                        <a target="_blank" href="https://' . TR_CA_HOST . '" style="text-decoration: none; color:#D9001B; cursor: pointer;">
                            ОТКРЫТЬ
                        </a>
                    </span>
                </p>
            </td>
            </tr>
        </tbody>
        </table>
        </td>
        </tr>
    </tbody>
</table>
<br>';

// email require sign
$MESS["TR_CA_DOCS_MAIL_EVENT_REQUIRED_SIGN_NAME"] = "КриптоАРМ Документы - уведомление о запросе подписи";
$MESS["TR_CA_DOCS_MAIL_EVENT_REQUIRED_SIGN_DESCRIPTION"] = "
#EMAIL# - EMail получателя сообщения
#FILE_NAME# - названия документов
#REQUESTING_USER# - пользователь, запросивший подпись
#FIO_TO# - кому сообщение
";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_REQUIRED_SIGN_SUBJECT"] = "#SITE_NAME#: Вам пришел запрос на подпись документа";
$MESS["TR_CA_DOCS_MAIL_TEMPLATE_REQUIRED_SIGN_BODY"] = '
<table cellspacing=0 style="padding: 16px; border:1px solid #c8c8c8; border-radius:4px">
    <tbody>
    <tr>
        <td>
        <table class="body" data-made-with-foundation>
            <tbody>
            <tr>
                <td rowspan="2">
                    <img class="img" src="' . TR_CA_DOCS_PATH_TO_POST_ICONS . 'favicon.ico" style="border-width: 0; width: 40px; height: 40px; padding-right: 8px;" alt="">
                </td>
                <td>
                    <p style="margin: 0; font-size: 20px;">
                    <span style="color:rgba(51, 51, 51, 0.866666666666667);">Вам документ на подпись (</span>
                    <span style="color:rgba(217, 0, 27, 0.866666666666667);">#FILE_NAMES#</span>
                    <span style="color:rgba(51, 51, 51, 0.866666666666667);">)</span>
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p style="margin: 0; color: rgba(0, 0, 0, 0.6); line-height: 20px;">
                        <span>КриптоАРМ Документы</span>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
        <table class="body" data-made-with-foundation>
            <tbody>
                <tr style="height: 36px">
                    <td>
                        <p style="margin: 0;">
                            <span>Здравствуйте,</span>
                            <span style="color:#D9001B;">#FIO_TO#</span>!
                        </p>
                    </td>
                </tr>

                <tr style="height: 36px">
                    <td>
                        <p style="margin: 0;">
                            <span style="color:#D9001B;">#REQUESTING_USER#</span>
                            <span>отправил вам на подпись документ "</span>
                            <span style="color:#D9001B;">#FILE_NAMES#</span>
                            <span>".</span>
                        </p>
                    </td>
                </tr>

                <tr style="height: 36px">
                    <td>
                        <p style="margin: 0;">
                            <span>Ознакомиться с документом и подписать его Вы можете авторизовавшись на сайте </span>
                            <span>
                                <a target="_blank" href="https://' . TR_CA_HOST . '" style="text-decoration: none; color: #D9001B; cursor: pointer;">
                                    https://' . TR_CA_HOST . '
                                </a>
                            </span>
                            <span>, используя (или создав) учетную запись с текущим адресом электронной почты в качестве логина или идентификатора (в случае создания)</span>
                        </p>
                    </td>
                </tr>

                <tr style="height: 72px">
                    <td>
                        <p style="margin: 0;">
                            <span>
                                <a href="#SIGN_URL#" style="text-decoration: none; color:#D9001B; cursor: pointer;">
                                    Подписать документы
                                </a>
                            </span>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        </td>
        </tr>
    </tbody>
</table>
<br>';

$MESS["TR_CA_DOCS_CANCEL_INSTALL"] = "Отменить установку";

$MESS["TR_CA_DOCS_BP_SIGN_TEMPLATE"] = "Подпись документа выбранными сотрудниками";
$MESS["TR_CA_DOCS_BP_AGREED_TEMPLATE"] = "Согласование документа выбранными сотрудниками";
$MESS["TR_CA_DOCS_BP_SERVICE_NOTE"] = "Служебная записка";
$MESS["TR_CA_DOCS_BP_ACQUAINTANCE"] = "Ознакомление с документом выбранными сотрудниками";
$MESS["TR_CA_DOCS_BP_MONEY_DEMAND"] = "Заявка на получение денежных средств";
$MESS["TR_CA_DOCS_BP_ORDER"] = "Приказ выбранным сотрудникам с прикреплением отчёта о выполнении";


$MESS["TR_CA_DOCS_START"] = "Старт";
$MESS["TR_CA_DOCS_STANDARD"] = "Стандарт";
$MESS["TR_CA_DOCS_SMALL_BUSINESS_OR_BUSINESS_REDACTION"] = "бизнес";
$MESS["TR_CA_DOCS_CORP_REDACTION"] = "Корпоративный";
$MESS["TR_CA_DOCS_ENTERPRISE_REDACTION"] = "Энтерпрайз";
$MESS["TR_CA_DOCS_CORP_REDACTION_CRM"] = "Интернет-магазин + CRM";
