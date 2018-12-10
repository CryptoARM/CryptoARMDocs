<?php
namespace Trusted\CryptoARM\Docs;
use Bitrix\Main\Config\Option;

class License
{

    public static function makeRequest($url, $data = null) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in License::makeRequest",
            "data" => "",
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        if ($data) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($curl);
        if (!curl_errno($curl)) {
            $responseList = json_decode($response, true);
            $info = curl_getinfo($curl);
            if ($info['http_code'] == 200) {
                $res = array(
                    "success" => true,
                    "message" => $responseList['message'],
                    "data" => $responseList['data'],
                );
            } else {
                $res['message'] = 'Request error: ' . $responseList['message'];
            }
        } else {
            $error = curl_error($curl);
            curl_close($curl);
            $res['message'] = 'CURL error: ' . $error;
        }
        return $res;
    }

    public static function registerAccountNumber() {
        return License::makeRequest(LICENSE_SERVICE_REGISTER_NEW_ACCOUNT_NUMBER);
    }


    public static function checkAccountBalance($accountNumber) {
        $url = LICENSE_SERVICE_ACCOUNT_CHECK_BALANCE . $accountNumber;
        return License::makeRequest($url);
    }

    public static function activateJwtToken($accountNumber, $jwt) {
        $url = LICENSE_SERVICE_ACTIVATE_CODE . $accountNumber;
        return License::makeRequest($url, array('jwt' => $jwt));
    }

    public static function getOneTimeLicense() {
        $url = LICENSE_SERVICE_ACCOUNT_GET_ONCE_JWT_TOKEN . LICENSE_ACCOUNT_NUMBER;
        return License::makeRequest($url);
    }

    public static function getAccountHistory($accountNumber) {
        $url = LICENSE_SERVICE_ACCOUNT_HISTORY . $accountNumber;
        return License::makeRequest($url, array('days' => '30'));
    }
}

