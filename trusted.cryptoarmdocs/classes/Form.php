<?php

namespace Trusted\CryptoARM\Docs;

class Form {
    public static function addIBlock($iBlockTypeId, $props, $userId) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.addIBlock",
        );

        $iBlock = new \CIBlockElement;

        $response = Array(
            "MODIFIED_BY" => $userId, // элемент изменен текущим пользователем
            "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
            "IBLOCK_ID" => $iBlockTypeId,
            "PROPERTY_VALUES" => $props,
            "NAME" => "Form",
            "ACTIVE" => "Y",
        );

        if ($iBlockId = $iBlock->Add($response)) {
            $res = array(
                "success" => true,
                "message" => "iBlock added",
                "id" => $iBlockId
            );
        }

        return $res;
    }

    public static function standardizationIBlockProps($props) {
        $someArray = array();
        foreach ($props as $key => $value) {
            if (stristr($key, "input_date_")) {
                $key = str_ireplace("input_date_", "", $key);
                $someArray[$key] = date_format(date_create($value), 'd.m.Y');
                continue;
            }
            if (stristr($key, "input_checkbox_")) {
                $key = str_ireplace("input_checkbox_", "", $key);
                $keyValue = preg_split("/\D/", $key);
                $someArray[$keyValue[0]][] = $keyValue[1];
                continue;
            }
            if (stristr($key, "input_text_")) {
                $key = str_ireplace("input_text_", "", $key);
                $someArray[$key] = $value;
                continue;
            }
            if (stristr($key, "input_number_")) {
                $key = str_ireplace("input_number_", "", $key);
                $someArray[$key] = $value;
                continue;
            }
            if (stristr($key, "input_radio_")) {
                $key = str_ireplace("input_radio_", "", $key);
                $someArray[$key] = $value;
                continue;
            }
            if (stristr($key, "input_html_")) {
                $key = str_ireplace("input_html_", "", $key);
                $someArray[$key] = $value;
                continue;
            }
            if (stristr($key, "input_file_id_")) {
                $key = str_ireplace("input_file_id_", "", $key);
                if ($value || $value === 0 || $value === 0.0 || $value === '0') {
                    $someArray[$key] = $value;
                }
                continue;
            }
        }

        return $someArray;
    }

    public static function addIBlockForm($iBlockTypeId, $props) {
        $res = array(
            'success' => false,
            'message' => 'Unknown error in addIBlockForm',
        );

        global $USER;
        if (!$USER->IsAuthorized()) {
            $res['message'] = 'No authorization';
            return $res;
        }

        $props = self::standardizationIBlockProps($props);

        $addResult = self::addIBlock($iBlockTypeId, $props, $USER->GetID());

        $res['success'] = true;
        $res['message'] = $addResult['message'];
        $res['data'] = $addResult['id'];

        return $res;
    }
}