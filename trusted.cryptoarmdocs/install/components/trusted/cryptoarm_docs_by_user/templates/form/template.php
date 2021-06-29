<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Trusted\Id;
use Trusted\Id\OAuth2;

//checks the name of currently installed core from highest possible version to lowest
$coreIds = [
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
];
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $coreId . "/";
    if (file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();


$token = Id\OAuth2::getFromSession();

if ($token && !Docs\Forms::isUserWithForm()) {
?>
<div class="dark_window" id="dark_window"></div>
    <form class = "trn_form" id="trn_form">
        <div style="width: 100%; height: 90px">
            <div class="trn_form_item" style="font-size:32px; justify-content:center;">
                Анкета инвестора AngelsDeck Club
            </div>
            <div class="trn_form_item" style="font-size:16px; justify-content:center;">
                Для продолжения работы с сервисом, пожалуйста заполните анкету
            </div>
        </div>
        <div id="trn_form_name" class="tcn_form_text_input_field trn_form_item">
            <div>Фамилия Имя Отчество</div>
            <input type=text id="trn_form_name_input">
        </div>
        <div id="trn_form_dob" class="trn_form_item " style="flex-wrap:wrap; height:100px; justify-content: space-between; width:60%">
            <div style="width:100%  ">Дата рождения</div>
                <div>
                    День
                    <select id="trn_day_select" style="height: 30px"><option id="trn_form_day" value="null" selected disabled></option></select>
                </div>
                <div>
                    Месяц
                    <select id="trn_month_select" style="height: 30px"><option id="trn_form_month" value="null" selected disabled></option></select>
                </div>
                <div>
                    Год
                    <select id="trn_year_select" style="height: 30px"><option id="trn_form_year" value="null" selected disabled></option></select>
                </div>
        </div>
        <div id="trn_form_pob" class="tcn_form_text_input_field trn_form_item">
            <div>Место рождения</div>
            <input type=text id="trn_form_pob_input">
        </div>
        <div id="trn_form_citizen" class="tcn_form_text_input_field trn_form_item">
            <div>Гражданство</div>
            <input type=text id="trn_form_citizen_input">
        </div>
        <div id="trn_form_passport" class="tcn_form_text_input_field_passport trn_form_item">
            <div style="width: 100%;">Гражданский паспорт</div>
            <label for="trn_form_passport_input_series">Серия</label>
            <input type=text id="trn_form_passport_input_series" style="width: 120px;">
            <label for="trn_form_passport_input_number">Номер</label>
            <input type=text id="trn_form_passport_input_number" style="width: 193px;">
            <label for="trn_form_passport_input_when">Когда выдан</label>
            <input type=text id="trn_form_passport_input_when" style="width: 240px;"><br>
            <label for="trn_form_passport_input_who">Кем выдан</label>
            <input type=text id="trn_form_passport_input_who" style="width: 760px;">
        </div>
        <div id="trn_form_ns" class="tcn_form_text_input_field trn_form_item">
            <div>Name Surname (имя и фамилия в точности как в загран паспорте) </div>
            <input type=text id="trn_form_ns_input">
        </div>
        <div id="trn_form_int_passport" class="tcn_form_text_input_field trn_form_item">
            <div>Номер загран паспорта</div>
            <input type=text id="trn_form_int_passport_input">
        </div>
        <div id="trn_form_id_number" class="tcn_form_text_input_field trn_form_item">
            <div>(I/D Number) </div>
            <input type=text id="trn_form_id_number_input">
        </div>
        <div id="trn_form_inn" class="tcn_form_text_input_field trn_form_item">
            <div>ИНН</div>
            <input type=text id="trn_form_inn_input">
        </div>
        <div id="trn_form_number" class="tcn_form_text_input_field trn_form_item">
            <div>Номер телефона</div>
            <input type=text id="trn_form_number_input">
        </div>
        <div id="trn_form_email" class="tcn_form_text_input_field trn_form_item">
            <div>Email</div>
            <input type=text id="trn_form_email_input">
        </div>
        <div id="trn_form_reg_address" class="tcn_form_text_input_field trn_form_item">
            <div>Регистрационный адрес</div>
            <input type=text id="trn_form_reg_address_input">
        </div>
        <div id="trn_form_fact_address" class="tcn_form_text_input_field trn_form_item">
            <div>Фактический адрес проживания</div>
            <input type=text id="trn_form_fact_address_input">
        </div>
        <div id="trn_form_income_source" class="tcn_form_text_input_field trn_form_item">
            <div>Основные источники текущего дохода</div>
            <input type=text id="trn_form_income_source_input">
        </div>
        <div id="trn_form_income_value" class="tcn_form_text_input_field trn_form_item">
            <div>Объем годового дохода</div>
            <input type=text id="trn_form_income_value_input">
        </div>
        <div id="trn_form_sof" class="tcn_form_text_input_field trn_form_item">
            <div>Основные источники происхождения инвестируемых средств </div>
            <input type=text id="trn_form_sof_input">
        </div>
        <div id="trn_form_funds_value" class="tcn_form_funds_radio_place trn_form_item" style="flex-wrap:wrap; height:100px">
            <div style="width:100%">Объем накопленного капитала</div>
            <input type="radio" name="funds_value" value="1" id="trn_form_funds_value_radio1">
            <label for="trn_form_funds_value_radio1" style="display:flex; align-items:center">До 0,5 млн.$</label>
            <input type="radio" name="funds_value" value="2" id="trn_form_funds_value_radio2">
            <label for="trn_form_funds_value_radio2" style="display:flex; align-items:center">от 0,5 до 1,0 млн.$</label>
            <input type="radio" name="funds_value" value="3" id="trn_form_funds_value_radio3">
            <label for="trn_form_funds_value_radio3" style="display:flex; align-items:center">от 1,0 до 5,0 млн.$</label>
            <input type="radio" name="funds_value" value="4" id="trn_form_funds_value_radio4">
            <label for="trn_form_funds_value_radio4" style="display:flex; align-items:center">свыше 5 млн.$</label>
        </div>
        <div id="trn_form_public" class="tcn_form_public_radio trn_form_item" style="flex-wrap:wrap; height:100px">
            <div style="width:100%">Являетесь ли Вы Публичным Должностным Лицом?</div>
            <input type="radio" name="form_is_public" value="1" id="trn_form_public_radio1">
            <label for="trn_form_public_radio1" style="display:flex; align-items:center">Да</label>
            <input type="radio" name="form_is_public" value="0" id="trn_form_public_radio2">
            <label for="trn_form_public_radio2" style="display:flex; align-items:center">Нет</label>
        </div>
        <div class="trn_form_item">
            <input type="checkbox" id="trn_form_agree">
            <label for="trn_form_agree">Я подтверждаю, что указанная выше информация является достоверной и точной, в случае ее изменения обязуюсь незамедлительно уведомит вас об этом.</label>
        </div>
        <div class="trn_form_item" style="justify-content:center; height:120px">
            <div class="trn_form_button trn_disable_button" id="trn_form_confirm_button">
                <div>Отправить</div>
            </div>
        </div>
    </form>
<?php
};
?>
<script>
    $('#trn_form').change(function(){
    if (isAllWritten()) {
        $("#trn_form_confirm_button").addClass("trn_able_button");
        $("#trn_form_confirm_button").click(function() {
            send();
        })
    } else {
        $("#trn_form_confirm_button").removeClass("trn_able_button");
        $("#trn_form_confirm_button").off('click');
    }
});
for (let day = 1; day <= 31; day++) {
    $('#trn_day_select').append($('<option>', {
        value: day,
        text: day
    }));
};
ind = 0;
['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'].forEach((month)=> {
    ind ++;
    $('#trn_month_select').append($('<option>', {
        value: ind,
        text: month
    }));
});
for (let year = 1920; year <= 2020; year++) {
    $('#trn_year_select').append($('<option>', {
        value: year,
        text: year
    }));
};

function checkDate() {
    if (['2', '4', '6', '9', '11'].includes($('#trn_month_select').val()) && ($('#trn_day_select').val() == '31')) {
        return false;
    }
    if (($('#trn_month_select').val() == '2') && (($('#trn_day_select').val() == '30') || (($('#trn_day_select').val() == '29') && (Number.parseInt($('#trn_year_select').val()) % 4 != 0)))) {
        return false;
    }
    return true;
}

function send() {
    if (isAllWritten()) {
        let data = {
            name: $("#trn_form_name_input").val(),
            birthday: $("#trn_day_select").val() + '.' + $("#trn_month_select").val() + '.' + $("#trn_year_select").val(),
            placeOfBirth: $("#trn_form_pob_input").val(),
            citizenhood: $("#trn_form_citizen_input").val(),
            passportSeries: $("#trn_form_passport_input_series").val(),
            passportNumber: $("#trn_form_passport_input_number").val(),
            passportWhen: $("#trn_form_passport_input_when").val(),
            passportWho: $("#trn_form_passport_input_who").val(),
            intName: $("#trn_form_ns_input").val(),
            intPassport: $("#trn_form_int_passport_input").val(),
            idNumber: $("#trn_form_id_number_input").val(),
            inn: $("#trn_form_inn_input").val(),
            phone: $("#trn_form_number_input").val(),
            email: $("#trn_form_email_input").val(),
            regAddress: $("#trn_form_reg_address_input").val(),
            factAddress: $("#trn_form_fact_address_input").val(),
            incomeSource: $("#trn_form_income_source_input").val(),
            incomeValue: $("#trn_form_income_value_input").val(),
            sof: $("#trn_form_sof_input").val(),
            fundsVal: $("input[type='radio'][name='funds_value']:checked").val(),
            isPublic: $("input[type='radio'][name='form_is_public']:checked").val()
        };
        console.log(data);
        $.ajax({
            url: AJAX_CONTROLLER + '?command=createForm',
            type: 'post',
            data: data,
            success: function () {
                trustedCA.reloadDoc();
            }
        })
        $('#dark_window').hide();
        $('#trn_form').hide();
    }
}

function validateEmail(input) {
    var emailField =  /\S+@\S+\.\S+/;
    return emailField.test(input);
}

function validatePhoneNumber(phone) {
    if (!(phone[0] == '+' || (/^[0-9]/.test(phone[0])))) {
        return false;
    }

    let str = phone.slice(1);

    if (!str.match(/^\d+$/)) {
        return false;
    }
    return true;
}

function validatePassportSeries(series) {
    series = series.replace(/\s/g, '');
    if (!((series.length == 4) && (series.match(/^\d+$/)))) {
        return false;
    }
    return true;
}

function validatePassportNumber(number) {
    if (!((number.length == 6) && (number.match(/^\d+$/)))) {
        return false;
    }
    return true;
}

function isAllWritten() {
    if ($("#trn_form_name_input").val().length < 1) {
        return false;
    }
    if ($("#trn_form_pob_input").val().length < 1) {
        return false;
    }
    if ($("#trn_form_citizen_input").val().length < 1) {
        return false;
    }
    if (!validatePassportSeries($("#trn_form_passport_input_series").val())) {
        return false;
    }
    if (!validatePassportNumber($("#trn_form_passport_input_number").val())) {
        return false;
    }
    if ($("#trn_form_passport_input_when").val().length < 1) {
        return false;
    }
    if ($("#trn_form_passport_input_who").val().length < 1) {
        return false;
    }
    if ($("#trn_form_ns_input").val().length < 1) {
        return false;
    }
    if ($("#trn_form_int_passport_input").val().length < 1) {
        return false;
    }
    if ($("#trn_form_id_number_input").val().length < 1) {
        return false;
    }
    if ($("#trn_form_inn_input").val().length < 1) {
        return false;
    }
    if ($("#trn_form_number_input").val().length < 1) {
        return false;
    }
    if (!(validatePhoneNumber($("#trn_form_number_input").val()))) {
        return false;
    }
    if ($("#trn_form_email_input").val().length < 1) {
        return false;
    }
    if (!(validateEmail($("#trn_form_email_input").val()))) {
        return false;
    }
    if ($("#trn_form_reg_address_input").val().length < 1) {
        return false;
    }
    if ($("#trn_form_fact_address_input").val().length < 1) {
        return false;
    }
    if ($("#trn_form_income_source_input").val().length < 1) {
        return false;
    }
    if ($("#trn_form_income_value_input").val().length < 1) {
        return false;
    }
    if ($("#trn_form_sof_input").val().length < 1) {
        return false;
    }
    if (!($("#trn_form_agree").prop("checked"))) {
        return false;
    }
    if ($("#trn_form_funds_value").children("input:radio:checked").length == 0) {
        return false;
    }
    if ($("#trn_form_public").children("input:radio:checked").length == 0) {
        return false;
    }
    if ($("#trn_day_select").val() == "null") {
        return false;
    }
    if ($("#trn_month_select").val() == "null") {
        return false;
    }
    if ($("#trn_year_select").val() == "null") {
        return false;
    }
    if (!checkDate()) {
        return false;
    }
    return true;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function removeMyFormFromDB() { //!!!!!!!!!!!!!!УДАЛИТЬ
    $.ajax({
        url: AJAX_CONTROLLER + '?command=removeFormFromDB',
        type: 'post'
    })
}

function addDataToForm() { //!!!!!!!!!!!!!!УДАЛИТЬ

    function randomDateOfBirth() {
        let randMonth = Math.floor(Math.random() * 12);
        let randDay = Math.floor(Math.random() * 28);
        let randYear = Math.floor(Math.random() * 90) + 1920;
        $("#trn_month_select option[value='" + randMonth +"']").attr("selected", "selected");
        $("#trn_year_select option[value='" + randYear +"']").attr("selected", "selected");
        $("#trn_day_select option[value='" + randDay +"']").attr("selected", "selected");
    }
    function randomRusTextValue(number) {
        let abc = "абвгдежзиёклмнопрстуфхцчшщъыьэюя";
        let rs = "";
        while (rs.length < number) {
            rs += abc[Math.floor(Math.random() * abc.length)];
        }
        return rs;
    }
    function randomTextValue(number) {
        let abc = "abcdefghijklmnopqrstuvwxyz";
        let rs = "";
        while (rs.length < number) {
            rs += abc[Math.floor(Math.random() * abc.length)];
        }
        return rs;
    }
    function randomNumberValue(number) {
        let abc = "0123456789";
        let rs = "";
        while (rs.length < number) {
            rs += abc[Math.floor(Math.random() * abc.length)];
        }
        return rs;
    }
    function randomRadio() {
        let publ = Math.round(Math.random());
        let funds = Math.floor(Math.random() * 4);
        $('input[name="form_is_public"][value="' + publ + '"]').prop('checked', true);
        $('input[name="funds_value"][value="' + funds + '"]').prop('checked', true);
    }
    randomDateOfBirth();
    randomRadio();
    $("#trn_form_name_input").val(randomRusTextValue(6) + ' ' + randomRusTextValue(6) + ' ' + randomRusTextValue(6));
    $("#trn_form_pob_input").val(randomRusTextValue(20));
    $("#trn_form_citizen_input").val(randomRusTextValue(15));
    $("#trn_form_passport_input_series").val(randomNumberValue(4));
    $("#trn_form_passport_input_number").val(randomNumberValue(6));
    $("#trn_form_passport_input_when").val(randomNumberValue(2)+'.'+randomNumberValue(2)+'.'+randomNumberValue(4));
    $("#trn_form_passport_input_who").val(randomRusTextValue(20));
    $("#trn_form_ns_input").val(randomTextValue(6) + ' ' + randomTextValue(6));
    $("#trn_form_int_passport_input").val(randomNumberValue(9));
    $("#trn_form_id_number_input").val(randomNumberValue(9));
    $("#trn_form_inn_input").val(randomNumberValue(12));
    $("#trn_form_number_input").val(randomNumberValue(11));
    $("#trn_form_email_input").val(randomTextValue(4) + '@' + randomTextValue(4) + '.' + randomTextValue(2));
    $("#trn_form_reg_address_input").val(randomRusTextValue(20));
    $("#trn_form_fact_address_input").val(randomRusTextValue(20));
    $("#trn_form_income_source_input").val(randomRusTextValue(20));
    $("#trn_form_income_value_input").val(randomNumberValue(10) + ' ' + randomTextValue('3'));
    $("#trn_form_sof_input").val(randomRusTextValue(20));
}


</script>