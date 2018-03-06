<?php

class AjaxCommand
{
    static function updateStatus($params)
    {
        $res = array("success" => false, "message" => "Unknown error in Ajax.updateStatus");
        $id = $params["id"];
        $doc = TDataBaseDocument::getDocumentById($id);
        if (!$doc) {
            $res['message'] = GetMessage('TRUSTEDNET_DOC_IDNOTFOUND');
            return $res;
        }
        $status = $_GET["status"];
        if ($doc->getStatus() && $doc->getStatus()->getValue() == DOC_STATUS_BLOCKED) {
            echo "update status  " . $status . ' DOC_STATUS_CANCEL ' . DOC_STATUS_CANCEL . '      ' . DOC_STATUS_ERROR;
            switch ($status) {
                case DOC_STATUS_CANCEL:
                    if (!$doc->getSigners()) {
                        TDataBaseDocument::removeStatus($doc->getStatus());
                    } else {
                        $doc->getStatus()->setValue(DOC_STATUS_NONE);
                        $doc->getStatus()->save();
                    }
                    AjaxSign::sendSetStatus($params["operationId"], -1, "Canceled");
                    $res['success'] = true;
                    break;
                case DOC_STATUS_ERROR:
                    $doc->getStatus()->setValue($status);
                    $doc->getStatus()->save();
                    AjaxSign::sendSetStatus($params["operationId"], -1, "Error");
                    $res['success'] = true;
                    break;
                default:
                    $res['message'] = GetMessage('TRUSTEDNET_DOC_STATUNKNWN');
            }
        } else {
            echo 'condition false';
            $res['message'] = GetMessage('TRUSTEDNET_DOC_STATCHG');
        }
        die();
        return $res;
    }

    /**
     *
     * @param mixed $params {id: Array(Number), logo: String, extra: String}
     * @param function $cb
     * @return mixed {success: Boolean; message: String}
     */
    static function sign($params)
    {
        $res = array(
            "success" => false,
            "message" => null,
            "code" => null,
        );
        $docsId = $params["id"];
        if (!isset($docsId)) {
            // No ids were given
            return $res;
        }
        $docsSent = new DocumentCollection();
        $docsNotFound = array();
        $docsFileNotFound = new DocumentCollection();
        $docsBlocked = new DocumentCollection();
        $docsSigned = new DocumentCollection();
        foreach ($docsId as &$id) {
            $doc = TDataBaseDocument::getDocumentById($id);
            if (!$doc) {
                // No doc with that id is found
                $docsNotFound[] = $id;
            } else {
                $doc = $doc->getLastDocument();
                if ($doc->getStatus() && $doc->getStatus()->getValue() == DOC_STATUS_BLOCKED) {
                    // Doc is blocked by previous operation
                    $docsBlocked->add($doc);
                } elseif (!$doc->checkFile()) {
                    // Associated file was not found on the disk
                    $docsFileNotFound->add($doc);
                } elseif (!checkDocByExtra($doc, $params["extra"])) {
                    // No need to sign doc based on it STATUS property
                    $docsSigned->add($doc);
                } else {
                    // Doc is ready to be sent
                    $docsSent->add($doc);
                }
            }
        }
        if ($docsSent->count()) {
            $res["docsSent"] = array();
            foreach($docsSent->getList() as $doc) {
                $res["docsSent"][] = array(
                    "filename" => $doc->getName(),
                    "id" => $doc->getId()
                );
            }
        }
        if ($docsNotFound) {
            $res["docsNotFound"] = $docsNotFound;
        }
        if ($docsFileNotFound->count()) {
            foreach($docsFileNotFound->getList() as $doc) {
                $res["docsFileNotFound"][] = array(
                    "filename" => $doc->getName(),
                    "id" => $doc->getId()
                );
            }
        }
        if ($docsBlocked->count()) {
            foreach($docsBlocked->getList() as $doc) {
                $res["docsBlocked"][] = array(
                    "filename" => $doc->getName(),
                    "id" => $doc->getId()
                );
            }
        }
        if ($docsSigned->count()) {
            foreach($docsSigned->getList() as $doc) {
                $res["docsSigned"][] = array(
                    "filename" => $doc->getName(),
                    "id" => $doc->getId()
                );
            }
        }
        if ($docsSent->count()) {
            $ajaxParams = AjaxParams::fromArray($params);
            $resp = json_decode(AjaxSign::sendSignRequest($docsSent, $ajaxParams), true);
            if ($resp["success"] == true) {
                $res["success"] = true;
                $res["code"] = $resp["code"];
                $res["message"] = $resp['message'];
                $list = $docsSent->getList();
                foreach ($list as $item) {
                    // Set doc status to block it
                    DocumentStatus::create($item, DOC_STATUS_BLOCKED);
                }
            } else {
                $res["message"] = getErrorMessageFromResponse($resp);
                $res["code"] = $resp["code"];
            }
        }

        return $res;
    }

    /**
     * Recieves signed file from signing client,
     * creates new document and updates type and status
     * of other documents accordingly
     *
     * Receives following info:
     *      id: id of the document that was sent for signing
     *      file: signed file
     *      signers: information about signer
     *      extra: additional information
     *
     * @param array $params
     * @param callable $cb
     * @return array
     */
    static function upload($params, $cb = uploadSignature)
    {
        $res = array("success" => false, "message" => "Unknown error in Ajax.upload");
        $doc = TDataBaseDocument::getDocumentById($params['id']);
        //if (beforeUploadSignature($doc, $params["token"]) !== false) {
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
                if ($cb) {
                    $cb($newDoc, $file, $extra);
                }
                $newDoc->save();
                // Drop "blocked" status of original doc
                AjaxCommand::unblock(
                    array("id" => array($params["id"]))
                );
                $res["success"] = true;
                $res["message"] = "File uploaded";
            } else {
                $res["message"] = "Document is not found";
            }
        } else {
            $res["message"] = "Canceled in beforeUploadSignature function";
        }
        return $res;
    }

    /**
     * Sets document status to BLOCKED
     * params:
     *      id: array of document ids
     *
     * @param array $params
     * @return array
     */
    function block($params)
    {
        $res = array("success" => true, "message" => "");
        $docsId = $params["id"];
        if (isset($docsId)) {
            foreach ($docsId as &$id) {
                $doc = TDataBaseDocument::getDocumentById($id);
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
     * Sets document status to NONE
     * params:
     *      id: array of document ids
     *
     * @param mixed $params
     * @return array
     */
    function unblock($params)
    {
        $res = array("success" => true, "message" => "");
        $docsId = $params["id"];
        if (isset($docsId)) {
            foreach ($docsId as &$id) {
                $doc = TDataBaseDocument::getDocumentById($id);
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
     * params:
     *      id: array of document ids
     *
     * @param array $params
     * @return array
     */
    function remove($params)
    {
        $res = array("success" => false, "message" => "Unknown error in Ajax.remove");
        $docsId = $params["id"];
        if (isset($docsId)) {
            // Try to find all docs in DB
            foreach ($docsId as &$id) {
                $doc = TDataBaseDocument::getDocumentById($id);
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
                $doc = TDataBaseDocument::getDocumentById($id);
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

    static function view($params)
    {
        $res = array("success" => false, "message" => "Unknown error in Ajax.view");
        $doc = TDataBaseDocument::getDocumentById($params['id']);
        if ($doc) {
            $last = $doc->getLastDocument();
            $ajaxParams = AjaxParams::fromArray($params);
            $res = AjaxSign::sendViewRequest($last, $ajaxParams);
        } else $res["message"] = "Document is not found";
        return $res;
    }

    /**
     * Checks if file exists on the disk
     * params:
     *      id: document id
     *
     * @param array $params
     * @return array
     */
    static function download($params)
    {
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.download",
            "filename" => ""
        );
        $doc = TDataBaseDocument::getDocumentById($params['id']);
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
     * Sends raw file in response
     * params:
     *      id: document id
     *
     * @param array $params
     * @param callable $cb
     */
    static function content($params, $cb = getContent)
    {
        $res = array("success" => false, "message" => "Unknown error in Ajax.content");
        $doc = TDataBaseDocument::getDocumentById($params['id']);
        if ($doc) {
            $last = $doc->getLastDocument();
            getContent($last, $params['token']);
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

    static function token($params)
    {
        $res = array("success" => true, "message" => "");
        try {
            $token = OAuth2::getFromSession();
            //$refreshToken = $token->getRefreshToken();
            //$token->refresh();
            $accessToken = $token->getAccessToken();
            $res["message"] = $accessToken;
        } catch (OAuth2Exception $ex) {
            header("HTTP/1.1 500 Internal Server Error");
            $res["message"] = $ex->message;
            echo json_encode($res);
            die();
        }
        return $res;
    }
}

