<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
if ($arParams["IBLOCK_ID"] == "default" or $arParams["IBLOCK_ID"] == null) die();
?>

<form enctype="multipart/form-data" id="crypto-arm-document__send-form" method="POST">
    <div class="crypto-arm-document__send-form">
        <div class="send-form-data">
            <?
            foreach ($arResult["PROPERTY"] as $key => $value) {
                ?>
                <div class="input-string">
                    <?
                    switch ($value["PROPERTY_TYPE"]) {
                        case "S":
                            {
                                switch ($value["USER_TYPE"]) {
                                    case "HTML" :
                                        {
                                            ?>
                                            <? echo htmlspecialchars_decode($value["DEFAULT_VALUE"]["TEXT"]); ?>
                                            <br/>
                                            <?
                                        }

                                        break;
                                    case "Date" :
                                        {
                                            echo $value["NAME"];
                                            ?>
                                            <input type="date"
                                                   id="<?= "input_text_" . $value["ID"] ?>"
                                                   name="<?= "input_text_" . $value["ID"] ?>"
                                                   value="<?= $value["DEFAULT_VALUE"] ?>"
                                            />
                                            <br/>
                                            <?
                                        }
                                        break;
                                    default :
                                        {
                                            echo $value["NAME"];
                                            ?>
                                            <input type="text"
                                                   id="<?= "input_text_" . $value["ID"] ?>"
                                                   name="<?= "input_text_" . $value["ID"] ?>"
                                                   value="<?= $value["DEFAULT_VALUE"] ?>"
                                                   placeholder="<?= $value["HINT"] ?>"
                                            />
                                            <br/>
                                            <?
                                        }
                                }
                            }
                            break;
                        case "N":
                            {
                                echo $value["NAME"];
                                switch ($value["CODE"]) {
                                    case "DOC_FILE" :
                                        {
                                            ?>
                                            <input type="file"
                                                   id="<?= "input_number_" . $value["ID"] ?>"
                                                   name="<?= "input_number_" . $value["ID"] ?>"
                                            />
                                            <br/>
                                            <?
                                        }
                                        break;
                                    default :
                                        {
                                            ?>
                                            <input type="number"
                                                   id="<?= "input_number_" . $value["ID"] ?>"
                                                   name="<?= "input_number_" . $value["ID"] ?>"
                                                   value="<?= $value["DEFAULT_VALUE"] ?>"
                                                   placeholder="<?= $value["HINT"] ?>"
                                            />
                                            <br/>
                                            <?
                                        }
                                }
                            }
                            break;
                        case "L":
                            {
                                echo $value["NAME"];
                                if ($value["MULTIPLE"] == "Y") {
                                    foreach ($value["ADDICTION"] as $key2 => $value2) {
                                        ?>
                                        <input type="checkbox"
                                               id="<?= "input_checkbox_" . $key2 ?>"
                                               name="<?= "input_checkbox_" . $key2 ?>"
                                        />
                                        <label for="<?= "input_checkbox_" . $key2 ?>"><?= $value2 ?></label>
                                        <br/>
                                        <?
                                    }
                                    break;
                                }
                                switch ($value["LIST_TYPE"]) {
                                    case "L" :
                                        {
                                            ?>
                                            <p>
                                                <select name="<?= "input_number_" . $value["ID"] ?>">
                                                    <?
                                                    foreach ($value["ADDICTION"] as $key2 => $value2) {
                                                        ?>
                                                        <option value="<?= $key2 ?>"><?= $value2 ?></option>
                                                        <?
                                                    }
                                                    ?>
                                                </select>
                                            </p>
                                            <br/>
                                            <?
                                        }
                                        break;
                                    case
                                    "C" :
                                        {
                                            foreach ($value["ADDICTION"] as $key2 => $value2) {
                                                ?>
                                                <div class="radioBTN">
                                                    <input type="radio"
                                                           name="<?= "input_radio_" . $value["ID"] ?>"
                                                           value="<?= $value2 ?>"/><?= $value2 ?>
                                                </div>
                                                <br/>
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
        </div>
        <div>
            <input type="button" value="Сохранить файл" onclick=""/>
        </div>
    </div>
</form>