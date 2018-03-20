<?php
namespace TrustedNet\Docs;

/**
 * Controllers for AJAX requests.
 *
 * Used for interaction of bitrix server with opened pages and signing client.
 */
class AjaxCommand
{
    /**
     *
     * @param array $params [id]: array of document ids
     *                      [extra]: additional information
     * @return array [success]: operation result status
     *               [message]: operation result message
     *               [docsToSign]: JSON representation of documents that are ready to be signed
     *               [docsNotFound]: array of ids that were not found in document database
     *               [docsFileNotFound]: documents for which associated file was not found on disk
     *               [docsBlocked]: documents blocked by previous operation
     *               [docsRoleSigned]: documents that were already signed by provided ROLE
     */
    static function sign($params)
    {
        Utils::debug($params, "PARAMS");
        $res = array(
            "success" => false,
            "message" => "Nothing to sign",
        );
        $docsId = $params["id"];
        if (!isset($docsId)) {
            $res["message"] = "No ids were given";
            return $res;
        }
        $docsToSign = new DocumentCollection();
        $docsNotFound = array();
        $docsFileNotFound = new DocumentCollection();
        $docsBlocked = new DocumentCollection();
        $docsRoleSigned = new DocumentCollection();
        foreach ($docsId as &$id) {
            $doc = DataBase::getDocumentById($id)->getLastDocument();
            if (!$doc) {
                // No doc with that id is found
                $docsNotFound[] = $id;
            } else {
                $doc = $doc->getLastDocument();
                if ($doc->getStatus() === DOC_STATUS_BLOCKED) {
                    // Doc is blocked by previous operation
                    $docsBlocked->add($doc);
                } elseif (!$doc->checkFile()) {
                    // Associated file was not found on the disk
                    $docsFileNotFound->add($doc);
                } elseif (!Utils::checkDocByExtra($doc, $params["extra"]["role"])) {
                    // No need to sign doc based on it ROLES property
                    $docsRoleSigned->add($doc);
                } else {
                    // Doc is ready to be sent
                    $docsToSign->add($doc);
                }
            }
        }
        if ($docsToSign->count()) {
            $res["docsToSign"] = $docsToSign->toJSON();
            $res["message"] = "Found documents to sign";
            $res["success"] = true;
        }
        if ($docsNotFound) {
            $res["docsNotFound"] = $docsNotFound;
        }
        if ($docsFileNotFound->count()) {
            foreach($docsFileNotFound->getList() as $doc) {
                $res["docsFileNotFound"][] = array(
                    "filename" => $doc->getName(),
                    "id" => $doc->getId(),
                );
            }
        }
        if ($docsBlocked->count()) {
            foreach($docsBlocked->getList() as $doc) {
                $res["docsBlocked"][] = array(
                    "filename" => $doc->getName(),
                    "id" => $doc->getId(),
                );
            }
        }
        if ($docsRoleSigned->count()) {
            foreach($docsRoleSigned->getList() as $doc) {
                $res["docsRoleSigned"][] = array(
                    "filename" => $doc->getName(),
                    "id" => $doc->getId(),
                );
            }
        }

        return $res;
    }

    // TODO: annotate
    static function getDocsToJSON($params)
    {
        Utils::debug($params, "PARAMS");
        $res = array(
            "success" => false,
            "message" => "Unknown error in AjaxCommand.getDocJSON",
        );
        $docsId = $params["id"];
        if (!isset($docsId)) {
            $res["message"] = "No ids were given";
            return $res;
        }
        $docColl = new DocumentCollection();
        foreach ($docsId as &$id) {
            $doc = DataBase::getDocumentById($id)->getLastDocument();
            $docColl->add($doc);
        }
        if ($docColl->count()) {
            $res["docs"] = $docColl->toJSON();
            $res["message"] = "Found documents";
            $res["success"] = true;
        }
        return $res;
    }

    /**
     * Recieves signed file from signing client through POST method.
     *
     * Creates new document and updates type and status of other documents accordingly.
     *
     * @param array $params [id]: document id
     *                      [signers]: information about signer
     *                      [extra]: additional information
     * @return array [success]: operation result status
     *               [message]: operation result message
     */
    static function upload($params)
    {
        $res = array(
            "success" => false,
            "message" => "Unknown error in AjaxCommand.upload",
        );
        // TODO: do not accept if document is not last
        $doc = DataBase::getDocumentById($params['id'])->getLastDocument();
        // TODO: add security check
        if (true) {
            if ($doc) {
                $newDoc = $doc->copy();
                $signers = urldecode($params["signers"]);
                $newDoc->setSigners($signers);
                $newDoc->setType(DOC_TYPE_SIGNED_FILE);
                $newDoc->setParent($doc);
                $file = $_FILES["file"];
                $extra = json_decode($params["extra"], true);
                Utils::roleHandler($newDoc, $extra);
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
        $res = array(
            "success" => true,
            "message" => "",
        );
        $docsId = $params["id"];
        if (isset($docsId)) {
            foreach ($docsId as &$id) {
                $doc = DataBase::getDocumentById($id);
                $doc->setStatus(DOC_STATUS_BLOCKED);
                $doc->save();
            }
        } else {
            $res["message"] = "No ids were given";
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
        $res = array("success" => true,
            "message" => "",
        );
        $docsId = $params["id"];
        if (isset($docsId)) {
            foreach ($docsId as &$id) {
                $doc = DataBase::getDocumentById($id);
                $doc->setStatus(DOC_STATUS_NONE);
                $doc->save();
            }
        } else {
            $res["message"] = "No ids were given";
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
        $res = array(
            "success" => false,
            "message" => "Unknown error in AjaxCommand.remove",
        );
        $docsId = $params["id"];
        if (!isset($docsId)) {
            $res["message"] = "No ids were given";
            return $res;
        }
        if (isset($docsId)) {
            // Try to find all docs in DB
            foreach ($docsId as &$id) {
                $doc = DataBase::getDocumentById($id);
                if ($doc) {
                    $lastDoc = $doc->getLastDocument();
                    if (!$lastDoc) {
                        $res["message"] = "No document with this ID";
                        $res["success"] = false;
                        return $res;
                    }
                } else {
                    $res["message"] = "No document with this ID";
                    $res["success"] = false;
                    return $res;
                }
            }
            foreach ($docsId as &$id) {
                $doc = DataBase::getDocumentById($id);
                $lastDoc = $doc->getLastDocument();
                $lastDoc->remove();
            }
            $res["message"] = "Document was removed";
            $res["success"] = true;
        } else {
            $res["message"] = "No ids were given";
            $res["success"] = false;
        }
        return $res;
    }

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
     * @param array $params [id]: document id
     * @return void
     */
    static function content($params)
    {
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.content",
        );
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

