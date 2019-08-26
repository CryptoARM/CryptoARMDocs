<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

function createHTMLElement($value) {
    ?>
    <input type="hidden"
           id="<?= "input_html_" . $value["ID"] ?>"
           name="<?= "input_html_" . $value["ID"] ?>"
           value="<?= $value["DEFAULT_VALUE"]["TEXT"] ?>"
    />
    <?
}

function createDataConsentElement($value) {
    ?>
    <div class="trca-sf-checkbox">
        <input type="checkbox"
               class="consentElement"
               id="<?= "input_data_consent_" . $value["ID"] ?>"
               name="<?= "input_data_consent_" . $value["ID"] ?>"
               data-msg="<?= $value["HINT"] ?>"
        />
        <label for="<?= "input_data_consent_" . $value["ID"] ?>"></label>
        <div class="trca-sf-checkbox-value">
            <?= htmlspecialchars_decode($value["DEFAULT_VALUE"]["TEXT"]) ?>
        </div>
    </div>
    <?
}

function createDateInput($value) {
    ?>
    <div class="trca-sf-date">
        <input type="date"
               id="<?= "input_date_" . $value["ID"] ?>"
               name="<?= "input_date_" . $value["ID"] ?>"
               value="<?= $value["DEFAULT_VALUE"] ?>"
            <? echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
        />
    </div>
    <?
}

function createTextInput($value) {
    ?>
    <div class="trca-sf-input">
        <input type="text"
               id="<?= "input_text_" . $value["ID"] ?>"
               name="<?= "input_text_" . $value["ID"] ?>"
               placeholder="<?= $value["HINT"] ?>"
               value="<?= $value["DEFAULT_VALUE"] ?>"
            <? echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
        />
        <div class="trca-sf-input-footer"></div>
    </div>
    <?
}

function createFileInput($value, $arResult) {
    $multiple = $value["MULTIPLE"] == "Y" ? "_Y" : "";
    ?>
    <div id="trca-sf-upload-button-<?= $value["ID"] ?>">
        <div class="trca-sf-upload-button-input"
             id="<?= 'trca-sf-upload-button-input-' . $value['ID'] . '_0' ?>">
            <div class="trca-sf-upload-input"
                 id="<?= 'trca-sf-upload-input-' . $value['ID'] . '_0' ?>">
                <input type="file"
                       id="<?= "input_file_" . $value["ID"] . "_0" . $multiple ?>"
                       name="<?= "input_file_" . $value["ID"] . "_0" . $multiple ?>"
                       onchange="checkSizeNReadNWrite(<?= $value['ID'] . ', 0,' . "'" . $multiple . "'" . ',' . $arResult["MAX_UPLOAD_FILE_SIZE"] ?>)"
                    <?
                    echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
                />
                <?= Loc::getMessage("TR_CA_DOCS_COMP_FORM_INPUT_FILE"); ?>
            </div>
            <div class="trca-sf-upload-file-button"
                 id="<?= 'trca-sf-upload-file-button-' . $value['ID'] . '_0' ?>">
                <div class="trca-sf-upload-file">
                    <div class="trca-sf-upload-file-icon">
                        <i class="material-icons">
                            insert_drive_file
                        </i>
                    </div>
                    <div class="trca-sf-upload-file-name"
                         id="<?= 'trca-sf-upload-file-name-' . $value['ID'] . '_0' ?>"></div>
                    <div class="trca-sf-upload-file-remove"
                        <?
                        if ($value["MULTIPLE"] == "Y") {
                            ?>
                            onclick="removeUploadFile(<?= $value['ID'] ?>, 0)"
                            <?
                        } else {
                            ?>
                            onclick="hideUploadFile(<?= $value['ID'] ?>)"
                            <?
                        }
                        ?>
                    >
                        <i class="material-icons">
                            close
                        </i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?
}

function createNumberInput($value) {
    ?>
    <div class="trca-sf-input-number">
        <input type="number"
               id="<?= "input_number_" . $value["ID"] ?>"
               name="<?= "input_number_" . $value["ID"] ?>"
               value="<?= $value["DEFAULT_VALUE"] ?>"
               placeholder="<?= $value["HINT"] ?>"
            <? echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
        />
        <div class="trca-sf-input-footer"></div>
    </div>
    <?
}

function createCheckBoxElement($value, $key) {
    foreach ($value["ADDITIONAL"] as $key2 => $value2) {
        ?>
        <div class="trca-sf-checkbox">
            <input type="checkbox"
                   id="<?= "input_checkbox_" . $key . "_" . $key2 ?>"
                   name="<?= "input_checkbox_" . $key . "_" . $key2 ?>"
            />
            <label for="<?= "input_checkbox_" . $key . "_" . $key2 ?>"></label>
            <div class="trca-sf-checkbox-value">
                <?= $value2 ?>
            </div>
        </div>
        <?
    }
}

function createSelectElement($value) {
    ?>
    <div class="trca-sf-selector">
        <select
                id="<?= "input_select_" . $value["ID"] ?>"
                name="<?= "input_select_" . $value["ID"] ?>">
            <?
            foreach ($value["ADDITIONAL"] as $key2 => $value2) {
                ?>
                <option value="<?= $key2 ?>"><?= $value2 ?></option>
                <?
            }
            ?>
        </select>
        <div class="trca-sf-drop-down"></div>
    </div>
    <?
}

function createRadioButtonElement($value) {
    foreach ($value["ADDITIONAL"] as $key2 => $value2) {
        ?>
        <div class="trca-sf-radioBTN">
            <input type="radio"
                   id="<?= "input_radio_" . $value["ID"] ?>"
                   name="<?= "input_radio_" . $value["ID"] ?>"
                   value="<?= $key2 ?>"
                <? echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
            />
            <label>
                <?= $value2 ?>
            </label>
        </div>
        <?
    }
}

// iframe for file upload
?>
<iframe id="trCaDocs__frame"
        name="trCaDocs__frame"
        style="display:none">
</iframe>

<form enctype="multipart/form-data" target="trCaDocs__frame" id="crypto-arm-document__send-form" method="POST"
      action="/bitrix/components/trusted/cryptoarm_docs_form/templates/.default/uploadDocs.php"
      onSubmit="consentToDataProcessing();">
    <div class="crypto-arm-document__send-form">
        <div class="send-form-data">
            <?
            foreach ($arResult["PROPERTY"] as $key => $value) {
                ?>
                <div class="input-string">
                    <?
                    if ($value["USER_TYPE"] !== "HTML") {
                        ?>
                        <div class="export-item-title">
                            <?= $value["NAME"] ?>
                        </div>
                        <?
                    }
                    switch ($value["PROPERTY_TYPE"]) {
                        // STRING
                        case "S":
                            {
                                switch ($value["USER_TYPE"]) {
                                    case "HTML" :
                                        {
                                            if (stristr($value["CODE"], "DATA_CONSENT")) {
                                                createDataConsentElement($value);
                                            } else {
                                                echo htmlspecialchars_decode($value["DEFAULT_VALUE"]["TEXT"]);
                                            }
                                            createHTMLElement($value);
                                        }
                                        break;
                                    case "Date" :
                                        {
                                            createDateInput($value);
                                        }
                                        break;
                                    default :
                                    {
                                        createTextInput($value);
                                    }
                                }
                            }
                            break;
                        // NUMBER
                        case "N":
                            {
                                if (stristr($value["CODE"], "DOC_FILE")) {
                                    createFileInput($value, $arResult);
                                } else {
                                    createNumberInput($value);
                                }
                            }
                            break;
                        // LIST
                        case "L":
                        {
                            if ($value["MULTIPLE"] == "Y") {
                                createCheckBoxElement($value, $key);
                                break;
                            }
                            switch ($value["LIST_TYPE"]) {
                                case "L" :
                                    {
                                        createSelectElement($value);
                                    }
                                    break;
                                case "C" :
                                    {
                                        createRadioButtonElement($value);
                                    }
                                    break;
                            }
                            break;
                        }
                    }
                    ?>
                </div>
                <?
            }
            if ($arParams["ENABLE_RECAPTCHA"] === "Y") {
                ?>
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                <div id="recaptcha-sf">
                    <div class="g-recaptcha" data-sitekey="<?= $arResult["RECAPTCHA_SITE_KEY"] ?>"></div>
                </div>
                <?
            }
            ?>
            <input type="hidden"
                   id="iBlock_id"
                   name="iBlock_id"
                   value="<?= $arParams["IBLOCK_ID"] ?>"
            />
            <input type="hidden"
                   id="send_email_to_user"
                   name="send_email_to_user"
                   value="<?= $arResult["SEND_EMAIL_TO_USER"] ?>"
            />
            <input type="hidden"
                   id="send_email_to_admin"
                   name="send_email_to_admin"
                   value="<?= $arResult["SEND_EMAIL_TO_ADMIN_ADDRESS"] ?>"
            />
        </div>
        <p>
        <div class="trca-sf-button-sign">
            <input type="submit"/>
            <?= Loc::getMessage("TR_CA_DOCS_COMP_FORM_BUTTON_SIGN"); ?>
        </div>
    </div>
</form>
