<?php
namespace TrustedNet\Docs;
use Bitrix\Main\Config\Option;

class License
{

    /**
     * Recieves access token from net.trusted.ru
     *
     * @return string
     */
    private static function GetAccessToken () {
        $res = array(
            "success" => false,
            "message" => "Unknown error in License::GetAccessToken",
            "accessToken" => "",
        );
        $username = Option::get(TN_DOCS_MODULE_ID, "TN_USERNAME", "");
        $password = Option::get(TN_DOCS_MODULE_ID, "TN_PASSWORD", "");
        $id = Option::get(TN_DOCS_MODULE_ID, "TN_CLIENT_ID", "");
        $secret = Option::get(TN_DOCS_MODULE_ID, "TN_CLIENT_SECRET", "");

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => "https://net.trusted.ru/idp/sso/oauth/token",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "grant_type=password&username=" . $username . "&password=" . $password,
                CURLOPT_HTTPHEADER => ["content-type: application/x-www-form-urlencoded"],
                CURLOPT_USERPWD => $id . ":" . $secret,
            )
        );

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ($error) {
            $res["message"] = "curl error: $error";
            return $res;
        }
        $code = $info["http_code"];
        if ($code !== 200) {
            $res["message"] = "HTTP response code: $code";
            return $res;
        }
        $accessToken = json_decode($response, true)["access_token"];
        if ($accessToken) {
            $res["success"] = true;
            $res["message"] = "Received token";
            $res["accessToken"] = $accessToken;
        }

        return $res;
    }

    /**
     * Fetches license for the specified product.
     *
     * @param string $aud License holder
     * @param string $sub Product name
     * @param int $exp License life time in minutes
     * @return string JWT token with license info
     */
    public static function GetTrustedNetLicense($aud = "Anonymous", $sub = "CryptoARM GOST", $exp = 5)
    {
        $res = array(
            "success" => false,
            "message" => "Unknown error in License::GetTrustedNetLicense",
            "data" => "",
        );
        if (!is_numeric($exp) || $exp <= 0) {
            $res["message"] = "Incorrect license lifetime $exp";
            return $res;
        }

        $tokenResponse = License::GetAccessToken();
        if (!$tokenResponse["success"]) {
            $res["message"] = $tokenResponse["message"];
            return $res;
        } else {
            $accessToken = $tokenResponse["accessToken"];
        }

        $query = http_build_query(array(
            "sub" => $sub,
            "aud" => $aud,
            "exp" => $exp * 1000 * 60,
            "operations" => "TLS, SIGN, DECRYPT, KEYGEN, CTGOSTCP, PKCS11, CERBER",
            "options" => 65535,
            "token" => $accessToken,
        ));

        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_URL => LICENSE_SERVICE_URL . "?" . $query,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            )
        );

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ($error) {
            $res["message"] = "curl error: $error";
            return $res;
        }
        $code = $info["http_code"];
        if ($code !== 200) {
            $res["message"] = "HTTP response code: $code";
            return $res;
        }

        $response = json_decode($response, true);

        $res["data"] = $response["data"];
        if ($response["success"]) {
            $res["success"] = true;
            $res["message"] = "License received";
        } else {
            $res["message"] = "License server returned error";
        }

        return $res;
    }

}

