<?php

function getErrorMessageFromResponse($response, $errCode, $errMessage)
{
    $message = $errMessage;
    if (!is_null($response)) {
        $message = isset($response["message"]) ? $response["message"] : $response["error"];
    }
    if (!is_null($response) or $errCode) {
        $respCode = isset($response["code"]) ? $response["code"] : $errCode;
        switch ($respCode) {
            // Unauthorized
            case 1: {
                $message = GetMessage('TRUSTEDNET_SIGN_STAT_NOAUTH1');
                break;
            }
            // Client not connected
            case 100: {
                $message = GetMessage('TRUSTEDNET_SIGN_STAT_NOCONNECT');
                break;
            }
            case 101: {
                break;
            }
            default:
                break;
        }
    }
    if (stristr($message, "Service balance exhausted")) $message = GetMessage('TRUSTEDNET_SIGN_STAT_NOSIGN');
    if (stristr($message, "Unknown client:")) $message = GetMessage('TRUSTEDNET_SIGN_STAT_NOAPP');
    return $message;
}

