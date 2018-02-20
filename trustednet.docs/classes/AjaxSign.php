<?php

class AjaxSign
{

    /**
     *
     * @param \DocumentCollection $docs
     * @param \AjaxParams $params
     */
    static function sendSignRequest($docs, $params = null)
    {
        $docsList = $docs->getList();
        $files = array();
        $rToken = AjaxSign::getRefreshToken();

        foreach ($docsList as &$doc) {
            $file = array("file" => $doc->jsonSerialize());
            $file["file"]["url"] = TRUSTED_URI_COMPONENT_SIGN_AJAX . '?command=content&id=' . $doc->getId() . '&token=' . $rToken;
            $files[] = $file;
        }
        $data = array(
            "files" => $files,
            "uploader" => TRUSTED_URI_COMPONENT_SIGN_AJAX . "?command=upload",
            "cancel" => TRUSTED_URI_COMPONENT_SIGN_AJAX . "?command=updateStatus&status=2",
            "error" => TRUSTED_URI_COMPONENT_SIGN_AJAX . "?command=updateStatus&status=3",
            "token" => $rToken
        );
        if ($params) {
            $list = $params->toArray();
            foreach ($list as $key => $value) {
                $data[$key] = $value;
            }
        }
        $json = json_encode($data);
        return AjaxSign::sendRequestClient("client/sign", $json);
    }

    static protected function getRefreshToken()
    {
        return AjaxSign::getToken()->getRefreshToken();
    }

    static protected function getToken()
    {
        try {
            $token = OAuth2::getFromSession();
            if (!$token) {
                echo json_encode(array(
                    "success" => false,
                    "message" => getErrorMessageFromResponse(null, 1, 'TRUSTEDNET_SIGN_STAT_NOAUTH'),
                    "code" => 1));
                die();
            }
        } catch (OAuth2Exception $e) {
            echo json_encode(array(
                "success" => false,
                "message" => getErrorMessageFromResponse(null, 2, $e->getMessage()),
                "code" => 0));
            die();
        }
        return $token;
    }

    static function sendRequestClient($command, $json)
    {
        $url = 'https://net.trusted.ru/trustedapp/rest/' . $command;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . AjaxSign::getAccessToken()));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        $data = array('data' => $json);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {

        } else {

        }
        curl_close($curl);

        return $result;
    }

    static protected function getAccessToken()
    {
        return AjaxSign::getToken()->getAccessToken();
    }

    static function sendViewRequest($doc, $params = null)
    {
        $file = $doc->jsonSerialize();
        $rToken = AjaxSign::getRefreshToken();
        $file["url"] = $file["file"]["url"] = TRUSTED_URI_COMPONENT_SIGN_AJAX . '?command=content&id=' . $doc->getId() . '&token=' . $rToken;
        $data = array(
            "file" => $file,
            "token" => AjaxSign::getRefreshToken()
        );
        if ($params) {
            $list = $params->toArray();
            foreach ($list as $key => $value) {
                $data[$key] = $value;
            }
        }
        $json = json_encode($data);
        return AjaxSign::sendRequestClient("client/view", $json);
    }

    static function sendSetStatus($operationId, $status = 1, $desc = "")
    {
        //return;
        //echo("operationId: ".$operationId.PHP_EOL);
        $data = array(
            "data" => $operationId,
            "status" => $status,
            "description" => $desc,
            "clientId" => TRUSTEDNET_SIGN_CLIENT_ID,
            "secret" => TRUSTEDNET_SIGN_CLIENT_SECRET
        );
        $url = 'https://net.trusted.ru/trustedapp/app/client/sign/setstatus';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        if (!curl_errno($curl)) {
            $info = curl_getinfo($curl);
            curl_close($curl);
            if ($info['http_code'] == 200) {

            } else {
                echo "Wrong HTTP response status " . $info['http_code'] . PHP_EOL;
                echo $result . PHP_EOL;
            }
        } else {
            curl_close($curl);
            $error = curl_error($curl);
            echo("CURL error: " . $error);
        }
        //print_r($data);
        return $result;
    }
}

