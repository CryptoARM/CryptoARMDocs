<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
if (!$arResult["compVisibility"]) { ?>
    <div id="trCaError">
        ERROR
    </div>
<? } else { ?>
    <iframe id="trCaDocs__frame"
            name="trCaDocs__frame"
            style="display:none">
    </iframe>
    <form enctype="multipart/form-data" target="trCaDocs__frame" id="crypto-arm-document__send-form" method="POST"
          action="/bitrix/components/trusted/cryptoarm_docs_send_form/templates/.default/uploadDocs.php">
        <div class="crypto-arm-document__send-form">
            <div class="send-form-data">
                <?
                foreach ($arResult["PROPERTY"] as $key => $value) {
                    ?>
                    <div class="input-string">
                        <?
                        if (!($value["USER_TYPE"] == "HTML")) {
                            ?>
                            <div class="export-item-title">
                                <?
                                echo $value["NAME"];
                                ?>
                            </div>
                            <?
                        }
                        switch ($value["PROPERTY_TYPE"]) {
                            case "S":
                                {
                                    switch ($value["USER_TYPE"]) {
                                        case "HTML" :
                                            {
                                                ?>
                                                <? echo htmlspecialchars_decode($value["DEFAULT_VALUE"]["TEXT"]); ?>
                                                <input type="hidden"
                                                       id="<?= "input_html_" . $value["ID"] ?>"
                                                       name="<?= "input_html_" . $value["ID"] ?>"
                                                       value="<?= $value["DEFAULT_VALUE"]["TEXT"] ?>"
                                                />
                                                <br/>
                                                <?
                                            }
                                            break;
                                        case "Date" :
                                            {
                                                ?>
                                                <div class="trca-sf-date">
                                                    <input type="date"
                                                        id="<?= "input_date_" . $value["ID"] ?>"
                                                        name="<?= "input_date_" . $value["ID"] ?>"
                                                        value="<?= $value["DEFAULT_VALUE"] ?>"
                                                        <? echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
                                                    />
                                                    <div class="trca-sf-input-footer"></div>
                                                </div>
                                                <?
                                            }
                                            break;
                                        default :
                                            {
                                                ?>
                                                <div class="trca-sf-input">
                                                    <input type="text"
                                                        id="<?= "input_text_" . $value["ID"] ?>"
                                                        name="<?= "input_text_" . $value["ID"] ?>"
                                                        value="<?= $value["DEFAULT_VALUE"] ?>"
                                                        placeholder="<?= $value["HINT"] ?>"
                                                        <? echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
                                                    />
                                                    <div class="trca-sf-input-footer"></div>
                                                </div>
                                                <?
                                            }
                                    }
                                }
                                break;
                            case "N":
                                {
                                    if (stristr($value["CODE"], "DOC_FILE")) {
                                        ?>
                                        <div class="trca-sf-download-button">
                                            <input type="file"
                                                id="<?= "input_file_" . $value["ID"] ?>"
                                                name="<?= "input_file_" . $value["ID"] ?>"
                                                <? echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
                                            />
                                            Добавить Файл
                                        </div>
                                        <input type="hidden"
                                               id="<?= "input_file_id_" . $value["ID"] ?>"
                                               name="<?= "input_file_id_" . $value["ID"] ?>"
                                        />
                                        <?
                                    } else {
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
                                }
                                break;
                            case "L":
                                {
                                    if ($value["MULTIPLE"] == "Y") {
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
                                        break;
                                    }
                                    switch ($value["LIST_TYPE"]) {
                                        case "L" :
                                            {
                                                ?>
                                                <div class="trca-sf-selector">
                                                    <select
                                                            id="<?= "input_number_" . $value["ID"] ?>"
                                                            name="<?= "input_number_" . $value["ID"] ?>">
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
                                            break;
                                        case
                                        "C" :
                                            {
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
                                            break;
                                    }
                                    break;
                                }
                        }
                        ?>
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
                       value="<?= $arResult["SEND_EMAIL_TO_ADMIN"] ?>"
                />
            </div>
            <p>
            <div>
                <input type="submit"
                       value="Подписать документы"
                />
            </div>
        </div>
    </form>
<? } ?>
