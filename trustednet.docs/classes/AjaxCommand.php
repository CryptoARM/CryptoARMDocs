<?php
namespace TrustedNet\Docs;

/**
 * Controllers for AJAX requests.
 *
 * Used for interaction of bitrix server with opened pages and signing client.
 */
class AjaxCommand
{
    //static function updateStatus($params)
    //{
    //    $res = array("success" => false, "message" => "Unknown error in Ajax.updateStatus");
    //    $id = $params["id"];
    //    $doc = DataBase::getDocumentById($id);
    //    if (!$doc) {
    //        $res['message'] = GetMessage('TRUSTEDNET_DOC_IDNOTFOUND');
    //        return $res;
    //    }
    //    $status = $_GET["status"];
    //    if ($doc->getStatus() && $doc->getStatus()->getValue() == DOC_STATUS_BLOCKED) {
    //        echo "update status  " . $status . ' DOC_STATUS_CANCEL ' . DOC_STATUS_CANCEL . '      ' . DOC_STATUS_ERROR;
    //        switch ($status) {
    //            case DOC_STATUS_CANCEL:
    //                if (!$doc->getSigners()) {
    //                    DataBase::removeStatus($doc->getStatus());
    //                } else {
    //                    $doc->getStatus()->setValue(DOC_STATUS_NONE);
    //                    $doc->getStatus()->save();
    //                }
    //                AjaxSign::sendSetStatus($params["operationId"], -1, "Canceled");
    //                $res['success'] = true;
    //                break;
    //            case DOC_STATUS_ERROR:
    //                $doc->getStatus()->setValue($status);
    //                $doc->getStatus()->save();
    //                AjaxSign::sendSetStatus($params["operationId"], -1, "Error");
    //                $res['success'] = true;
    //                break;
    //            default:
    //                $res['message'] = GetMessage('TRUSTEDNET_DOC_STATUNKNWN');
    //        }
    //    } else {
    //        echo 'condition false';
    //        $res['message'] = GetMessage('TRUSTEDNET_DOC_STATCHG');
    //    }
    //    die();
    //    return $res;
    //}

    /**
     * Recieves signed file from signing client through POST method.
     *
     * Creates new document and updates type and status of other documents accordingly.
     *
     * @param array $params [id]: document id,
     *                      [signers]: information about signer,
     *                      [extra]: additional information
     * @return array [success]: operation result status
     *               [message]: operation result message
     */
    static function upload($params)
    {
        $res = array("success" => false, "message" => "Unknown error in Ajax.upload");
        $doc = DataBase::getDocumentById($params['id']);
        // TODO: add security check
        if (true) {
            if ($doc) {
                $newDoc = $doc->copy();
                $signers = urldecode($params["signers"]);
                if ($doc->getSigners()) {
                    // TODO: rewrite
                    $signers = substr($doc->getSigners(), 0 , -1) .','. substr($signers, 1);
                }
                $newDoc->setSigners($signers);
                $newDoc->setType(DOC_TYPE_SIGNED_FILE);
                $newDoc->setParent($doc);
                $file = $_FILES["file"];
                $extra = json_decode($params["extra"], true);
                TSignUtils::roleHandler($newDoc, $extra);
                if ($newDoc->getParent()->getType() == DOC_TYPE_FILE) {
                    $newDoc->setName($newDoc->getName() . '.sig');
                    $newDoc->setPath($newDoc->getPath() . '.sig');
                }
                $newDoc->save();
                move_uploaded_file(
                    $file['tmp_name'],
                    $_SERVER['DOCUMENT_ROOT'] . '/' . rawurldecode($newDoc->getPath())
                );
                // Drop "blocked" status of original doc
                $doc = DataBase::getDocumentById($params['id']);
                $doc->setStatus(DOC_STATUS_NONE);
                $doc->save();
                $res["success"] = true;
                $res["message"] = "File uploaded";
            } else {
                $res["message"] = "Document is not found";
            }
        } else {
            $res["message"] = "Access denied";
        }
        return $res;
    }

    /**
     * Sets document status to BLOCKED for one or multiple documents.
     *
     * @param array $params [id]: array of document ids
     * @return array [success]: operation result status
     *               [message]: operation result message
     */
    function block($params)
    {
        $res = array("success" => true, "message" => "");
        $docsId = $params["id"];
        if (isset($docsId)) {
            foreach ($docsId as &$id) {
                $doc = DataBase::getDocumentById($id);
                $doc->setStatus(DOC_STATUS_BLOCKED);
                $doc->save();
            }
        } else {
            $res["message"] = GetMessage('TRUSTEDNET_DOC_POSTIDREQ');
            $res["success"] = false;
        }
        return $res;
    }

    /**
     * Sets document status to NONE for one or multiple documents
     *
     * @param array $params [id]: array of document ids
     * @return array [success]: operation result status
     *               [message]: operation result message
     */
    function unblock($params)
    {
        $res = array("success" => true, "message" => "");
        $docsId = $params["id"];
        if (isset($docsId)) {
            foreach ($docsId as &$id) {
                $doc = DataBase::getDocumentById($id);
                $doc->setStatus(DOC_STATUS_NONE);
                $doc->save();
            }
        } else {
            $res["message"] = GetMessage('TRUSTEDNET_DOC_POSTIDREQ');
            $res["success"] = false;
        }
        return $res;
    }

    /**
     * Removes documents and their parents from DB
     *
     * @param array $params [id]: array of document ids
     * @return array [success]: operation result status
     *               [message]: operation result message
     */
    function remove($params)
    {
        $res = array("success" => false, "message" => "Unknown error in Ajax.remove");
        $docsId = $params["id"];
        if (isset($docsId)) {
            // Try to find all docs in DB
            foreach ($docsId as &$id) {
                $doc = DataBase::getDocumentById($id);
                if ($doc) {
                    $lastDoc = $doc->getLastDocument();
                    if (!$lastDoc) {
                        $res["message"] = GetMessage('TRUSTEDNET_DOC_IDNOTFOUND');
                        $res["success"] = false;
                        return $res;
                    }
                } else {
                    $res["message"] = GetMessage('TRUSTEDNET_DOC_IDNOTFOUND');
                    $res["success"] = false;
                    return $res;
                }
            }
            foreach ($docsId as &$id) {
                $doc = DataBase::getDocumentById($id);
                $lastDoc = $doc->getLastDocument();
                $lastDoc->remove();
            }
            $res["message"] = GetMessage('TRUSTEDNET_DOC_REMOVE_SUCCESS');
            $res["success"] = true;
        } else {
            $res["message"] = GetMessage('TRUSTEDNET_DOC_POSTIDREQ');
            $res["success"] = false;
        }
        return $res;
    }

    //static function view($params)
    //{
    //    $res = array("success" => false, "message" => "Unknown error in Ajax.view");
    //    $doc = DataBase::getDocumentById($params['id']);
    //    if ($doc) {
    //        $last = $doc->getLastDocument();
    //        $ajaxParams = AjaxParams::fromArray($params);
    //        $res = AjaxSign::sendViewRequest($last, $ajaxParams);
    //    } else $res["message"] = "Document is not found";
    //    return $res;
    //}

    /**
     * Checks if file exists on the disk
     *
     * @param array $params [id]: document id,
     * @return array [success]: operation result status
     *               [message]: operation result message
     */
    static function download($params)
    {
        // TODO: rename
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.download",
            "filename" => ""
        );
        $doc = DataBase::getDocumentById($params['id']);
        if ($doc) {
            $last = $doc->getLastDocument();
            $res["filename"] = $last->getName();
            if ($last->checkFile()) {
                $res["success"] = true;
                $res["message"] = "File found";
                return $res;
            } else {
                $res["message"] = "File not found";

            }
        } else {
            $res["message"] = "Document no found";
        }
        return $res;
    }

    /**
     * Initiates file transfer
     *
     * @param array $params [id]: document id,
     * @return void
     */
    static function content($params)
    {
        $res = array("success" => false, "message" => "Unknown error in Ajax.content");
        $doc = DataBase::getDocumentById($params['id']);
        if ($doc) {
            $last = $doc->getLastDocument();
            $file = $_SERVER["DOCUMENT_ROOT"] . urldecode($last->getPath());
            if (file_exists($file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $doc->getName() . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                readfile($file);
            }
        } else {
            echo 'file not exist';
            header("HTTP/1.1 500 Internal Server Error");
            $res["message"] = "Document is not found";
            echo json_encode($res);
            die();
        }
    }

    //static function token($params)
    //{
    //    $res = array("success" => true, "message" => "");
    //    try {
    //        $token = OAuth2::getFromSession();
    //        //$refreshToken = $token->getRefreshToken();
    //        //$token->refresh();
    //        $accessToken = $token->getAccessToken();
    //        $res["message"] = $accessToken;
    //    } catch (OAuth2Exception $ex) {
    //        header("HTTP/1.1 500 Internal Server Error");
    //        $res["message"] = $ex->message;
    //        echo json_encode($res);
    //        die();
    //    }
    //    return $res;
    //}
}

