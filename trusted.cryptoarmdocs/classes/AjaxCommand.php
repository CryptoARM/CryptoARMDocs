<?php

namespace Trusted\CryptoARM\Docs;
use DateTime;

/**
 * Controllers for AJAX requests.
 *
 * Used for interaction of bitrix server with opened pages and signing client.
 */
class AjaxCommand {
    /**
     * Recieves array of document ids and checks them all before
     * determining which ones are ready to signed.
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
    static function sign($params) {
        $res = array(
            "success" => false,
            "message" => "Nothing to sign",
        );

        if (!Utils::checkAuthorization()) {
            $res["message"] = "No authorization";
            return $res;
        }

        $docIds = $params["id"];
        if (!$docIds) {
            $res["message"] = "No ids were given";
            return $res;
        }

        $docsToSign = new DocumentCollection();
        $docsNotFound = array();
        $docsFileNotFound = new DocumentCollection();
        $docsBlocked = new DocumentCollection();
        $docsRoleSigned = new DocumentCollection();
        $docsNoAccess = array();

        foreach ($docIds as &$id) {
            $doc = Database::getDocumentById($id);
            if (!$doc) {
                // No doc with that id is found
                $docsNotFound[] = $id;
            } else {
                $doc = $doc->getLastDocument();
                if (!$doc->accessCheck(Utils::currUserId(), DOC_SHARE_SIGN)) {
                    // Current user has no access to the doc
                    $docsNoAccess[] = $id;
                } elseif ($doc->getStatus() === DOC_STATUS_BLOCKED) {
                    // Doc is blocked by previous operation
                    $docsBlocked->add($doc);
                } elseif (!$doc->checkFile()) {
                    // Associated file was not found on the disk
                    $docsFileNotFound->add($doc);
                } elseif ($params["extra"] && !DocumentsByOrder::checkDocByRole($doc, $params["extra"]["role"])) {
                    // TODO: probably should be removed
                    // No need to sign doc based on it ROLES property
                    $docsRoleSigned->add($doc);
                } else {
                    // Doc is ready to be sent
                    $docsToSign->add($doc);
                    $doc->setStatus(DOC_STATUS_BLOCKED);
                    $doc->save();
                }
            }
        }

        if ($docsToSign->count()) {
            $res["docsToSign"] = $docsToSign->toJSON();
            $res["message"] = "Some documents were sent for signing";
            $res["success"] = true;
        }
        if ($docsNotFound) {
            $res["docsNotFound"] = $docsNotFound;
        }
        if ($docsFileNotFound->count()) {
            foreach ($docsFileNotFound->getList() as $doc) {
                $res["docsFileNotFound"][] = array(
                    "filename" => $doc->getName(),
                    "id" => $doc->getId(),
                );
            }
        }
        if ($docsBlocked->count()) {
            foreach ($docsBlocked->getList() as $doc) {
                $res["docsBlocked"][] = array(
                    "filename" => $doc->getName(),
                    "id" => $doc->getId(),
                );
            }
        }
        if ($docsRoleSigned->count()) {
            foreach ($docsRoleSigned->getList() as $doc) {
                $res["docsRoleSigned"][] = array(
                    "filename" => $doc->getName(),
                    "id" => $doc->getId(),
                );
            }
        }
        if ($docsNoAccess) {
            $res["docsNoAccess"] = $docsNoAccess;
        }

        if ($res['success'] && PROVIDE_LICENSE) {
            $license = License::getOneTimeLicense();
            if (!$license['success']) {
                $res['message'] .= '. License fetch error';
                $res['license'] = null;
            } else {
                $res['license'] = $license['data'];
            }
        }

        return $res;
    }

    /**
     * Returns document info in JSON format to send
     * to the signing progamm.
     *
     * @param array $params [id]: array of document ids
     * @return array [success]: operation result status
     *               [message]: operation result message
     *               [docs]: document info in JSON format
     */
    static function verify($params) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in AjaxCommand.verify",
        );

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No autorization';
            return $res;
        }

        $docIds = $params["id"];
        if (!$docIds) {
            $res["message"] = "No ids were given";
            return $res;
        }

        $docColl = new DocumentCollection();
        foreach ($docIds as &$id) {
            $doc = Database::getDocumentById($id);
            if ($doc && $doc->accessCheck(Utils::currUserId(), DOC_SHARE_READ)) {
                $docColl->add($doc->getLastDocument());
            }
        }
        if ($docColl->count()) {
            $res["docs"] = $docColl->toJSON();
            $res["message"] = "Found documents";
            $res["success"] = true;
        } else {
            $res["message"] = "Nothing to verify";
        }
        return $res;
    }

    /**
     * Recieves signed file from signing client through POST method.
     *
     * Creates new document and updates type and status of other documents accordingly.
     *
     * @param array $params [id]: document id
     *                      [signers]: information about signatures
     *                      [extra]: additional information
     * @return array [success]: operation result status
     *               [message]: operation result message
     */
    static function upload($params) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in AjaxCommand.upload",
        );

        // TODO: add security check
        if (true) {
            $doc = Database::getDocumentById($params['id']);
            if ($doc) {
                $lastDoc = $doc->getLastDocument();
            } else {
                $res["message"] = "Document is not found";
                return $res;
            }
            if ($lastDoc->getId() != $doc->getId()) {
                $res["message"] = "Document already has child.";
                return $res;
            }

            $newDoc = $doc->copy();
            $signatures = urldecode($params["signers"]);
            $newDoc->setSignatures($signatures);
            $newDoc->setType(DOC_TYPE_SIGNED_FILE);
            $newDoc->setParent($doc);
            $file = $_FILES["file"];
            $newDoc->setHash(hash_file('md5', $file['tmp_name']));
            $extra = json_decode($params["extra"], true);
            // Detect document by order signing
            if (array_key_exists("role", $extra)) {
                DocumentsByOrder::upload($newDoc, $extra);
            }
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
            $doc = Database::getDocumentById($params['id']);
            $doc->setStatus(DOC_STATUS_NONE);
            $doc->save();
            $res["success"] = true;
            $res["message"] = "File uploaded";
        } else {
            $res["message"] = "Access denied";
        }
        Utils::log(array(
            "action" => "signed",
            "docs" => $doc,
            "extra" => $params["extra"],
        ));
        return $res;
    }

    /**
     * Sets document status to BLOCKED for one or multiple documents.
     *
     * @param array $params [id]: array of document ids
     * @return array [success]: operation result status
     *               [message]: operation result message
     */
    static function block($params) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in AjaxCommand.block",
        );

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No autorization';
            return $res;
        }

        $docIds = $params["ids"];
        if (!$docIds) {
            $res["message"] = "No ids were given";
            return $res;
        }

        $res["message"] = "No access";
        foreach ($docIds as &$id) {
            $doc = Database::getDocumentById($id);
            if ($doc && $doc->accessCheck(Utils::currUserId(), DOC_SHARE_SIGN)) {
                $res["success"] = true;
                $res["message"] = "Some documents were blocked";
                $doc->setStatus(DOC_STATUS_BLOCKED);
                $doc->save();
            }
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
    static function unblock($params) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in AjaxCommand.unblock",
        );

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No autorization';
            return $res;
        }

        $docIds = $params["ids"];
        if (!$docIds) {
            $res["message"] = "No ids were given";
            return $res;
        }

        $res["message"] = "No access";
        foreach ($docIds as &$id) {
            $doc = Database::getDocumentById($id);
            if ($doc && $doc->accessCheck(Utils::currUserId(), DOC_SHARE_SIGN)) {
                $res["success"] = true;
                $res["message"] = "Some documents were unblocked";
                $doc->setStatus(DOC_STATUS_NONE);
                $doc->save();
            }
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
    static function remove($params) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in AjaxCommand.remove",
        );

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No autorization';
            return $res;
        }

        $docIds = $params["ids"];
        if (!$docIds) {
            $res["docsNotFound"] = array($id);
            $res["message"] = "No ids were given";
            return $res;
        }

        // Check all docs before removing
        foreach ($docIds as &$id) {
            $doc = Database::getDocumentById($id);
            if (!$doc) {
                $res["docsNotFound"] = array($id);
                $res["message"] = "No access to some of the documents";
                return $res;
            }

            $lastDoc = $doc->getLastDocument();
            if (!$lastDoc) {
                $res["docsNotFound"] = array($id);
                $res["message"] = "No access to some of the documents";
                return $res;
            }

            if (!$lastDoc->accessCheck(Utils::currUserId())) {
                $res["docsNotFound"] = array($id);
                $res["message"] = "No access to some of the documents";
                return $res;
            }
        }

        foreach ($docIds as &$id) {
            $doc = Database::getDocumentById($id);
            $lastDoc = $doc->getLastDocument();
            $lastDoc->remove();
            Utils::log(array(
                "action" => "removed",
                "docs" => $lastDoc,
            ));
        }

        $res["message"] = "Some documents were removed";
        $res["success"] = true;
        return $res;
    }

    /**
     * Checks if files exist on the disk
     *
     * @param array $params [ids]: document ids
     * @return array [success]: operation result status
     *               [message]: operation result message
     */
    static function download($params) {
        // TODO: rename or merge with content
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.download",
        );

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No autorization';
            return $res;
        }

        $ids = $params["ids"];
        if (!$ids) {
            $res["message"] = "No ids were given";
            return $res;
        }

        $docsFound = new DocumentCollection();
        $docsNotFound = array();
        $docsFileNotFound = new DocumentCollection();
        $docsNoAccess = array();

        foreach ($ids as $id) {
            $doc = Database::getDocumentById($id);
            if ($doc) {
                $doc = $doc->getLastDocument();
                if (!$doc->accessCheck(Utils::currUserId(), DOC_SHARE_READ)) {
                    $docsNoAccess[] = $id;
                } elseif ($doc->checkFile()) {
                    $docsFound->add($doc);
                } else {
                    $docsFileNotFound->add($doc);
                }
            } else {
                $docsNotFound[] = $id;
            }
        }

        if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/upload/tmp/TCA-DocsTmp/")) {
            mkdir($_SERVER["DOCUMENT_ROOT"] . "/upload/tmp/TCA-DocsTmp/", 0744);
        }

        if ($docsFound->count()) {
            $filename = $params["filename"] ? $params["filename"] . ".zip" : "TCA-Docs.zip";
            $archivePath = $_SERVER["DOCUMENT_ROOT"] . "/" . $filename;
            $archiveObject = \CBXArchive::GetArchive($archivePath);
            $archiveObject->SetOptions(
                array(
                    "REMOVE_PATH" => $_SERVER["DOCUMENT_ROOT"],
                )
            );
            $docsFoundPaths = array();
            foreach ($docsFound->getList() as $doc) {
                $docPath = urldecode($_SERVER['DOCUMENT_ROOT'] . $doc->getHtmlPath());
                $docsFoundPaths[] = $docPath;
            }
            $archiveObject->Pack($docsFoundPaths);
        }

        if ($docsNotFound) {
            $res["docsNotFound"] = $docsNotFound;
        }

        if ($docsFileNotFound->count()) {
            $res["docsFileNotFound"] = array();
            foreach ($docsFileNotFound->getList() as $doc) {
                $res["docsFileNotFound"][] = array(
                    "filename" => $doc->getName(),
                    "id" => $doc->getId(),
                );
            }
        }

        if ($docsNoAccess) {
            $res["docsNoAccess"] = $docsNoAccess;
        }

        if (file_exists($archivePath)) {
            rename($archivePath, $_SERVER["DOCUMENT_ROOT"] . "/upload/tmp/TCA-DocsTmp/" . $filename);
        }

        if ($docsFound->count()) {
            $res["success"] = true;
            $res["message"] = "Some document files were found";
        } else {
            $res["message"] = "Nothing to download";
        }
        $res["content"] = $filename;
        return $res;
    }

    /**
     * Sends document file
     *
     * @param array $params [id]: document id
     *                      [file]: path to file
     * @return void
     */
    static function content($params) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.content",
        );
        if ($params["id"]) {
            $doc = Database::getDocumentById($params['id']);
            if ($doc) {
                $last = $doc->getLastDocument();
                $file = $last->getFullPath();
                Utils::download($file, $doc->getName());
            } else {
                header("HTTP/1.1 500 Internal Server Error");
                $res["message"] = "Document is not found";
                echo json_encode($res);
                die();
            }
        } elseif ($params["file"]) {
            Utils::download($_SERVER["DOCUMENT_ROOT"] . "/upload/tmp/TCA-DocsTmp/" . $params["file"], $params["file"]);
        } else {
            $res["message"] = "No argument given";
            echo json_encode($res);
            die();
        }
    }


    /**
     * Registers new account in licensesvc.
     *
     * @return array [success]: operation result status
     *               [message]: operation result message
     *               [data]: string ascii, account number
     */
    static function registerAccountNumber() {
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.registerAccountNumber",
        );

        $accountNumberData = License::registerAccountNumber();

        if ($accountNumberData['success']) {
            $res = array(
                "success" => true,
                "data" => $accountNumberData['data'],
                "message" => "OK",
            );
        }

        return $res;
    }

    /**
     * Returns number of operations on the account.
     *
     * @param $params [accountNumber]: string ascii
     * @return array [success]: operation result status
     *               [message]: operation result message
     *               [data]: int, number of operations on the account
     */
    static function checkAccountBalance($params) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.checkAccountBalance",
        );

        $accountNumber = $params['accountNumber'];
        $balanceData = License::checkAccountBalance($accountNumber);

        if ($balanceData['success']) {
            $res = array(
                "success" => true,
                "data" => $balanceData['data'],
                "message" => "OK",
            );
        }

        return $res;
    }

    /**
     * Adds operation to the account.
     *
     * @param $params [accountNumber]: string ascii
     * @return array [success]: operation result status
     *               [message]: operation result message
     *               [data]: error code on result false
     *               [data][amount]: number of added operations
     */
    static function activateJwtToken($params) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.activateJwtToken",
        );

        $accountNumber = $params['accountNumber'];
        $jwt = $params['jwt'];
        $balanceData = License::activateJwtToken($accountNumber, $jwt);

        if ($balanceData['success']) {
            $res = array(
                "success" => true,
                "data" => $balanceData['data'],
                "message" => "OK",
            );
        }

        return $res;
    }

    /**
     * Returns formatted log of all operation in n days
     * of the specified account.
     *
     * @param $params [accountNumber]: string ascii
     *                [days]: int, number of days
     * @return string
     */
    static function getAccountHistory($params) {
        $accountNumber = $params['accountNumber'];
        $days = $params['days'];
        $history = License::getAccountHistory($accountNumber, $days);

        if ($history['success']) {
            $strHistory = "";
            foreach ($history['data'] as $elemHistory) {
                $timeInUTC = $elemHistory['timestamp'];
                $dt = new DateTime($timeInUTC, new \DateTimeZone('UTC'));
                $dt->setTimezone(new \DateTimeZone($params['timeZone']));
                $realTime = $dt->format('Y-m-d H:i:s T');
                $strHistory .= $realTime . " ";
                $strHistory .= $elemHistory['operation'] . " ";
                $strHistory .= $elemHistory['userIP'] . " ";
                $strHistory .= $elemHistory['userName'] . "\n";
            }
        }
        return $strHistory;
    }


    static function sendEmail($params) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.sendEmail",
        );

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No autorization';
            return $res;
        }

        $ids = $params['ids'];
        $event = $params['event'];
        $arEventFields = $params['arEventFields'];
        $messageId = $params['messageId'];

        foreach ($ids as $docId) {
            $doc = Database::getDocumentById($docId);
            if (!$doc || !$doc->accessCheck(Utils::currUserId(), DOC_SHARE_READ)) {
                $res["message"] = "No access to some of the documents";
                return $res;
            }
        }

        $sendStatus = Email::sendEmail($ids, $event, $arEventFields, $messageId);

        if ($sendStatus['success']) {
            $res = array(
                "success" => true,
                "message" => "Email sent successfully",
            );
        } else {
            $res["message"] = $sendStatus["message"];
        }

        return $res;
    }

    static function share($params) {
        $res = array(
            "success" => false,
            "message" => "Unknown error in Ajax.share",
        );

        if (!Utils::checkAuthorization()) {
            $res["message"] = "No autorization";
            return $res;
        }

        $docIds = $params["ids"];
        $email = $params["email"];
        $level = $params["level"];

        $userId = Utils::getUserIdByEmail($email);
        if (!$userId) {
            $res["message"] = "User not found";
            return $res;
        }

        foreach ($docIds as $docId) {
            $doc = Database::getDocumentById($docId);
            if (!$doc || !$doc->accessCheck(Utils::currUserId())) {
                $res["message"] = "No access to some of the documents";
                return $res;
            }

            $fileName = $doc->getName();
            $ownerId = $doc->getOwner();
            $shareFrom = Utils::getUserName($ownerId) ?: "";

            $arEventFields = array(
                "EMAIL" => $email,
                "FILE_NAME" => $fileName,
                "SHARE_FROM" => $shareFrom,
            );

            Email::sendEmail([$docId], "MAIL_EVENT_ID_SHARE", $arEventFields, "MAIL_TEMPLATE_ID_SHARE");
            $doc->share($userId, $level);
            $doc->save();
        }

        $res["success"] = true;
        $res["message"] = "Documents shared";
        return $res;
    }
}

