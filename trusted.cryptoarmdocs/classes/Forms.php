<?php

namespace Trusted\CryptoARM\Docs;
use \Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;


class Forms {
    public static function removeForm($userId = false) {
        global $DB;
        if (!$userId) {
            $userId = Docs\Utils::currUserId();
        }
        $sql = 'DELETE FROM `tr_id_form` WHERE (USER_ID=' . $userId . ')';
        $DB -> Query($sql);
    }
    public static function newForm($params) {
        global $DB;
        $userId = Docs\Utils::currUserId();
        $sql = 'INSERT INTO `tr_id_form` (USER_ID,FIO,birthday,pob ,citizenhood ,passport_siries, passport_number, passport_when, passport_who, int_name ,int_passport ,id_number ,inn,phone,email,reg_address,fact_address,income_source,income_value,sof,founds_value,is_public)';
        $sql .= ' VALUES (' . $userId . ',"' . $params['name'] . '",';
        $sql .= '"' . $params['birthday'] . '",';
        $sql .= '"' . $params['placeOfBirth'] . '",';
        $sql .= '"' . $params['citizenhood'] . '",';
        $sql .= '"' . $params['passportSeries'] . '",';
        $sql .= '"' . $params['passportNumber'] . '",';
        $sql .= '"' . $params['passportWhen'] . '",';
        $sql .= '"' . $params['passportWho'] . '",';
        $sql .= '"' . $params['intName'] . '",';
        $sql .= '"' . $params['intPassport'] . '",';
        $sql .= '"' . $params['idNumber'] . '",';
        $sql .= '"' . $params['inn'] . '",';
        $sql .= '"' . $params['phone'] . '",';
        $sql .= '"' . $params['email'] . '",';
        $sql .= '"' . $params['regAddress'] . '",';
        $sql .= '"' . $params['factAddress'] . '",';
        $sql .= '"' . $params['incomeSource'] . '",';
        $sql .= '"' . $params['incomeValue'] . '",';
        $sql .= '"' . $params['sof'] . '",';
        $sql .=  $params['fundsVal'] . ',';
        $sql .=  $params['isPublic'] . ')';
        $DB -> Query($sql);
    }

    public static function isUserWithForm() {
        global $DB;
        $userId = Docs\Utils::currUserId();
        $sql = 'SELECT ID FROM tr_id_form WHERE USER_ID=' . $userId;
        $rows = $DB->Query($sql);
        return ($rows->SelectedRowsCount()==0)?false:true;
    }

    public static function createPDFAgreement($params) {
        require_once TR_CA_DOCS_MODULE_DIR_CLASSES . 'tcpdf_min/tcpdf.php';
        $pdf = new \TCPDF(
            'P',        // orientation - [P]ortrait or [L]andscape
            'mm',       // measure unit
            'A4',       // page format
            true,       // unicode
            'UTF-8',    // encoding for conversions
            false,      // cache, deprecated
            false       // pdf/a mode
        );

        $author = Loc::getMessage('TR_CA_DOC_MODULE_NAME');
        $title = "Согласие на обработку персональных данных";

        $pdf->setCreator($author);
        $pdf->setAuthor($author);
        $pdf->setTitle($title);
        $pdf->setSubject($title);
        $pdf->setKeywords('CryptoARM, document, digital signature');
        $pdf->setHeaderFont(['dejavuserif', 'B', 11]);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(18, PDF_MARGIN_TOP, 18);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('dejavuserif', '', 10);
        $pdf->AddPage();

        $pdfHTML = '<span>Согласие на обработку персональных данных</span><br>';
        $pdfHTML .= '<span>Я, <u style="width:100%; border-bottom: 1px solid grey">' . $params['name'] . '</u>,</span><br>';
        $pdfHTML .= '<span><span style="color:white">Документ, удостоверяющий личн  </span>(фамилия, имя, отчество)</span><br>';
        $pdfHTML .= '<span>Документ, удостоверяющий личность <u>  паспорт  </u>  № <u>' . $params['passportSeries'] . ' ' . $params['passportNumber'] .'</u></span><br>';
        $pdfHTML .= '<span><span style="color:white">Документ, удостоверяющий личн  </span>(вид документа)</span><br>';
        $pdfHTML .= '<span>выдан<u> ' . $params['passportWho'] . '  ' . $params['passportWhen'] .'</u></span><br>';
        $pdfHTML .= '<span><span style="color:white">Документ, удостоверяющий личн  </span>(кем и когда)</span><br>';
        $pdfHTML .= '<span>зарегистрированный (ая) по адресу:</span><br>';
        $pdfHTML .= '<span><u>' . $params['regAddress'] . '</u></span><br>';
        $pdfHTML .= '<span>согласен (а) на обработку моих персональных данных: (фамилия, имя, отчество;</span><br>';
        $pdfHTML .= '<span>дата рождения; контактный телефон (дом., мобильный, рабочий); адрес </span><br>';
        $pdfHTML .= '<span>проживания; сведения о регистрации, информацию о банковских счетах,</span><br>';
        $pdfHTML .= '<span>информацию об источниках доходов, информацию об источнике инвестируемых </span><br>';
        $pdfHTML .= '<span>средств) индивидуальным предпринимателем Калошиным Игорем Валерьевичем </span><br>';
        $pdfHTML .= '<span>зарегистрированным за Основным государственным регистрационным номером </span><br>';
        $pdfHTML .= '<span>Индивидуального предпринимателя (ОГРНИП) 319392600049717, по адресу:</span><br>';
        $pdfHTML .= '<span>236039, Российская Федерация, Калининградская область, город Калининград, </span><br>';
        $pdfHTML .= '<span>улица Багратиона, д. 148, кв. 21, в лице Калошина Игоря Валерьевича, </span><br>';
        $pdfHTML .= '<span>действующего на основании Устава, с целью обработки материалов на сервисе </span><br>';
        $pdfHTML .= '<span>AngelsDeck. Субъект дает согласие на обработку Оператором своих персональных </span><br>';
        $pdfHTML .= '<span>данных, то есть совершение, в том числе, следующих действий: обработку </span><br>';
        $pdfHTML .= '<span>(включая сбор, систематизацию, накопление, хранение, уточнение (обновление, </span><br>';
        $pdfHTML .= '<span>изменение), использование, обезличивание, блокирование, уничтожение </span><br>';
        $pdfHTML .= '<span>персональных данных), при этом общее описание вышеуказанных способов </span><br>';
        $pdfHTML .= '<span>обработки данных приведено в Федеральном законе от 27.07.2006 № 152-ФЗ « О </span><br>';
        $pdfHTML .= '<span>персональных данных», а также на передачу такой информации третьим лицам, в </span><br>';
        $pdfHTML .= '<span>случаях, установленных нормативными документами вышестоящих органов и </span><br>';
        $pdfHTML .= '<span>законодательством. Настоящее согласие действует до достижения цели обработки </span><br>';
        $pdfHTML .= '<span>персональных данных.</span><br>';
        $pdfHTML .= '<span>Настоящее согласие может быть отозвано мною в любой момент по соглашению </span><br>';
        $pdfHTML .= '<span>сторон. В случае неправомерного использования предоставленных данных </span><br>';
        $pdfHTML .= '<span>согласие отзывается письменным заявлением.</span><br>';
        $pdfHTML .= '<span>Подтверждаю, что ознакомлен (а) с положениями Федерального закона от </span><br>';
        $pdfHTML .= '<span>27.07.2006 №152-ФЗ «О персональных данных», права и обязанности в области </span><br>';
        $pdfHTML .= '<span>защиты персональных данных мне разъяснены.</span><br>';
        $pdf->writeHTMLCell(
            0,      // width
            0,      // height
            '',     // x
            '',     // y
            $pdfHTML,
            0,      // border
            1,      // next line
            0,      // fill
            true,   // reset height
            '',     // align
            true    // autopadding
        );
        $title .= '.pdf';

        $DOCUMENTS_DIR = Option::get(TR_CA_DOCS_MODULE_ID, 'DOCUMENTS_DIR', '/docs/');

        $uniqid = (string)uniqid();
        $newDocDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/';
        mkdir($newDocDir);

        $newDocDir .= $title;
        $relativePath = '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/' . $title;


        $pdf->Output($newDocDir, 'F');
        $props = new PropertyCollection();
        $props->add(new Property("USER", (string)Utils::currUserId()));
        $doc = Utils::createDocument($relativePath, $props);
        $docId = $doc->GetId();
        global $DB;
        $sql = 'UPDATE `tr_id_form` SET agreement_id='. $docId . ' WHERE USER_ID=' . Docs\Utils::currUserId();
        $DB->Query($sql);
        return $docId;
    }

    public static function createPDFForm($params) {
        require_once TR_CA_DOCS_MODULE_DIR_CLASSES . 'tcpdf_min/tcpdf.php';
        $pdf = new \TCPDF(
            'P',        // orientation - [P]ortrait or [L]andscape
            'mm',       // measure unit
            'A4',       // page format
            true,       // unicode
            'UTF-8',    // encoding for conversions
            false,      // cache, deprecated
            false       // pdf/a mode
        );

        $author = Loc::getMessage('TR_CA_DOC_MODULE_NAME');
        $title = "Анкета инвестора";
        $title = Utils::mb_basename($title);

        $pdf->setCreator($author);
        $pdf->setAuthor($author);
        $pdf->setTitle($title);
        $pdf->setSubject($title);
        $pdf->setKeywords('CryptoARM, document, digital signature');
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('dejavuserif', '', 11);
        $pdf->AddPage();


        $datetime = getdate();
        $day = $datetime['mday'];
        $month = $datetime['mon'];
        $year = $datetime['year'];
        $date = $day . '.' . $month . '.' . $year;

        $pdfText = '<h1>Анкета инвестора</h1>';
        $pdfText .= '<table style="border: 1px solid grey;">';
        $pdfText .= '<tbody>';
        $pdfText .= '<tr style="border: 1px solid grey;height:40"><td colspan="2" style="border: 1px solid grey;">ФИО: ' . $params['name'] . '</td></tr>';
        $pdfText .= '<tr style="border: 1px solid grey;height:40"><td style="border: 1px solid grey;"> Дата рождения: ' . $params['birthday'] . '<br>';
        $pdfText .= 'Место рождения: ' . $params['placeOfBirth'] . '</td>';
        $pdfText .= '<td style="border: 1px solid grey;">Гражданство: ' . $params['citizenhood'] . '</td></tr>';
        $pdfText .= '<tr style="border: 1px solid grey;height:40"><td colspan="2" style="border: 1px solid grey;">Серия и номер паспорта, кем и когда выдан: ' . $params['passportSeries'] . ' ' . $params['passportNumber'] . '  ' . $params['passportWhen'] . '  ' . $params['passportWho'] . '</td></tr>';
        $pdfText .= '<tr style="border: 1px solid grey;height:40"><td colspan="2" style="border: 1px solid grey;">Name Surname (имя и фамилия в точности как в загран паспорте): ' . $params['intName'] . '</td></tr>';
        $pdfText .= '<tr style="border: 1px solid grey;height:40"><td colspan="2" style="border: 1px solid grey;">Номер загран паспорта: ' . $params['intPassport'] . '</td></tr>';
        $pdfText .= '<tr style="border: 1px solid grey;height:40"><td style="border: 1px solid grey;">(I/D Number): ' . $params['idNumber'] . '<br>';
        $pdfText .= 'ИНН: ' . $params['inn'] . '</td>';
        $pdfText .= '<td style="border: 1px solid grey;">Номер телефона: ' . $params['phone'] . '<br>';
        $pdfText .= 'Email: ' . $params['email'] . '</td></tr>';
        $pdfText .= '<tr style="border: 1px solid grey;height:40"> <td colspan="2" style="border: 1px solid grey;">Регистрационный адрес: ' . $params['regAddress'] . '</td></tr>';
        $pdfText .= '<tr style="border: 1px solid grey;height:40"><td colspan="2" style="border: 1px solid grey;">Фактический адрес проживания:' . $params['factAddress'] . '</td></tr>';
        $pdfText .= '<tr style="border: 1px solid grey;height:40"><td colspan="2" style="border: 1px solid grey;">Основные источники текущего дохода: ' . $params['incomeSource'] . '<br>';
        $pdfText .= 'Объем годового дохода: ' . $params['incomeValue'] . '</td></tr>';
        $pdfText .= '<tr style="border: 1px solid grey;height:40"><td colspan="2" style="border: 1px solid grey;">Основные источники происхождения инвестируемых средств: ' . $params['sof'] . '<br>';
        $pdfText .= 'Объем накопленниого капитала: <br>';
        switch($params['fundsVal']) {
            case '1':
                $pdfText .= 'До 0,5 млн. $';
                break;
            case '2':
                $pdfText .= 'от 0,5 до 1,0 млн.$';
                break;
            case '3':
                $pdfText .= 'от 1,0 до 5,0 млн.$';
                break;
            case '4':
                $pdfText .= 'свыше 5 млн.$';
                break;
        }
        $pdfText .= '</td></tr>';
        $pdfText .= '<tr style="border: 1px solid grey;height:40"><td colspan="2">Являетесь ли Вы Публичным Должностным Лицом?  <br>';
        switch($params['isPublic']) {
            case '1':
                $pdfText .= 'Да';
                break;
            case '0':
                $pdfText .= 'Нет';
                break;
        }
        $pdfText .= '</td></tr></tbody></table><br><br>';
        $pdfText .= '<span>Я подтверждаю, что указанная выше информация является достоверной и точной, в случае ее изменения обязуюсь незамедлительно уведомит вас об этом.</span><br><br>';
        $pdfText .= 'Дата:<u>' . $date . '</u>';
        $pdf->writeHTMLCell(
            0,      // width
            0,      // height
            '',     // x
            '',     // y
            $pdfText,
            0,      // border
            1,      // next line
            0,      // fill
            true,   // reset height
            '',     // align
            true    // autopadding
        );
        // $pdf->writeHTML($pdfText, true, false, true, false, '');
        $title .= '.pdf';

        $DOCUMENTS_DIR = Option::get(TR_CA_DOCS_MODULE_ID, 'DOCUMENTS_DIR', '/docs/');

        $uniqid = (string)uniqid();
        $newDocDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/';
        mkdir($newDocDir);

        $newDocDir .= $title;
        $relativePath = '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/' . $title;


        $pdf->Output($newDocDir, 'F');
        $props = new PropertyCollection();
        $props->add(new Property("USER", (string)Utils::currUserId()));
        $doc = Utils::createDocument($relativePath, $props);
        $docId = $doc->GetId();
        global $DB;
        $sql = 'UPDATE `tr_id_form` SET blank_id=' . $docId . ' WHERE USER_ID=' . Docs\Utils::currUserId();
        $DB->Query($sql);
        return $docId;
    }

    public static function getBlankId($userId = false) {
        if (!$userId) {
            $userId = Docs\Utils::currUserId();
        }

        global $DB;
        $sql = 'SELECT blank_id FROM `tr_id_form` WHERE USER_ID=' . $userId;
    }
}
