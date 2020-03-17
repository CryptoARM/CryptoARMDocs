<?php

namespace Trusted\CryptoARM\Docs;

use Bitrix\Main\Loader;
use DateTime;
use Bitrix\Main\ModuleManager;

/**
 * Controllers for AJAX requests.
 *
 * Used for interaction of bitrix server with opened pages and signing client.
 */
class AjaxCommand {

    /**
     * Check if documents are available before acessing them.
     *
     * @param array [ids]: array of document ids
     *              [level]: access level required for operation
     *              [allowBlocked]: can operation be performed on blocked docs?
     */
    static function check($params) {
        $res = [
            'success' => false,
            'message' => 'Unknown error in Ajax.check',
        ];

        if (!Utils::checkAuthorization()) {
            $res["message"] = "No authorization";
            $res["noAuth"] = true;
            return $res;
        }

        $ids = $params['ids'];
        $level = $params['level'] ? : DOC_SHARE_READ;
        $allowBlocked = $params['allowBlocked'] ? : true;

        if (!$ids) {
            $res["message"] = "No ids were given";
            $res["noIds"] = true;
            return $res;
        }

        $res = array_merge(
            $res,
            Utils::checkDocuments($ids, $level, $allowBlocked)
        );

        $res['docsFileNotFound'] = $res['docsFileNotFound']->toIdAndFilenameArray();
        $res['docsBlocked'] = $res['docsBlocked']->toIdAndFilenameArray();
        $res['docsOk'] = $res['docsOk']->toIdArray();

        if ($res['docsOk']) {
            $res['success'] = true;
            $res['message'] = 'Some documents passed checks';
        } else {
            $res['message'] = 'Documents did not pass checks';
        }

        return $res;
    }

    /**
     * Recieves array of document ids and checks them all before
     * determining which ones are ready to signed.
     *
     * @param array $params [id]: array of document ids
     *                      [extra]: additional information
     * @return array [success]: operation result status
     *               [message]: operation result message
     *               [token]: block token
     *               [docsOk]: JSON representation of documents that are ready to be signed
     *               [docsNotFound]: array of ids that were not found in document database
     *               [docsFileNotFound]: documents for which associated file was not found on disk
     *               [docsBlocked]: documents blocked by previous operation
     *               [docsRoleSigned]: documents that were already signed by provided ROLE
     */
    static function sign($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.sign",
        ];

        if (!Utils::checkAuthorization()) {
            $res["message"] = "No authorization";
            $res["noAuth"] = true;
            return $res;
        }

        $ids = $params["id"];
        if (!$ids) {
            $res["message"] = "No ids were given";
            $res["noIds"] = true;
            return $res;
        }

        $res = array_merge(
            $res,
            Utils::checkDocuments($ids, DOC_SHARE_SIGN, false, true)
        );

        $token = Utils::generateUUID();
        $res["token"] = $token;
        $res["signType"] = TR_CA_DOCS_TYPE_SIGN;

        foreach ($res['docsOk']->getList() as $okDoc) {
            $okDoc->setSignType(TR_CA_DOCS_TYPE_SIGN);
            $okDoc->block($token);
            $okDoc->save();
        }

        $res['docsFileNotFound'] = $res['docsFileNotFound']->toIdAndFilenameArray();
        $res['docsBlocked'] = $res['docsBlocked']->toIdAndFilenameArray();
        if ($res['docsOk']->count()) {
            $res['docsOk'] = $res['docsOk']->toJSON();
        } else {
            $res['docsOk'] = null;
        }

        if ($res['docsOk']) {
            $res["success"] = true;
            $res["message"] = "Some documents were sent for signing";
        } else {
            $res["message"] = "Nothing to sign";
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
        $res = [
            "success" => false,
            "message" => "Unknown error in AjaxCommand.verify",
        ];

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No autorization';
            $res["noAuth"] = true;
            return $res;
        }

        $ids = $params["id"];

        if (!$ids) {
            $res["message"] = "No ids were given";
            $res["noIds"] = true;
            return $res;
        }

        $res = array_merge(
            $res,
            Utils::checkDocuments($ids, DOC_SHARE_READ, true, false)
        );

        $res['docsFileNotFound'] = $res['docsFileNotFound']->toIdAndFilenameArray();
        $res['docsBlocked'] = $res['docsBlocked']->toIdAndFilenameArray();
        $res['docsUnsigned'] = $res['docsUnsigned']->toIdAndFilenameArray();
        if ($res['docsOk']->count()) {
            $res['docsOk'] = $res['docsOk']->toJSON();
        } else {
            $res['docsOk'] = null;
        }

        if ($res['docsOk']) {
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
        $res = [
            "success" => false,
            "message" => "Unknown error in AjaxCommand.upload",
        ];

        $doc = Database::getDocumentById($params['id']);

        if (isset($params["success"]) && !$params["success"]) {
            $doc->unblock();
            $doc->save();
        }

        if ($doc) {
            $lastDoc = $doc->getLastDocument();
        } else {
            $res["message"] = "Document is not found";
            return $res;
        }
        if ($lastDoc->getId() !== $doc->getId()) {
            $res["message"] = "Document already has child.";
            return $res;
        }
        if ($doc->getStatus() !== DOC_STATUS_BLOCKED) {
            $res["message"] = "Document not blocked";
            return $res;
        }
        $extra = json_decode($params["extra"], true);
        if ($doc->getBlockToken() !== $extra['token']) {
            $res["message"] = "Wrong token";
            return $res;
        }

        if ($doc->getId() !== $doc->getOriginalId() && $doc->getSignType() !== $extra["signType"]) {
            $res["message"] = "Wrong sign type";
            return $res;
        }

        $doc->setSignType($extra["signType"]);
        $doc->save();
        $newDoc = $doc->copy();
        $signatures = urldecode($params["signers"]);
        $newDoc->setSignatures($signatures);
        // Append new user to the list of signers
        $newDoc->addSigner($doc->getBlockBy());
        $newDoc->setType(DOC_TYPE_SIGNED_FILE);
        $newDoc->setParent($doc);
        $file = $_FILES["file"];
        $newDoc->setHash(hash_file('md5', $file['tmp_name']));
        // Detect document by order signing
        if (array_key_exists("role", $extra)) {
            DocumentsByOrder::upload($newDoc, $extra);
        }

        $requires = $newDoc->getRequires()->getList();
        foreach ($requires as &$require) {
            if ($require->getUserId() == $doc->getBlockBy()) {
                $require->setSignStatus(true);
            }
        }

        if ($newDoc->getParent()->getType() == DOC_TYPE_FILE) {
            $newDoc->setName($newDoc->getName() . '.sig');
            $newDoc->setPath($newDoc->getPath() . '.sig');
        }
        $newDoc->setSignType($extra["signType"]);
        $newDoc->save();
        move_uploaded_file(
            $file['tmp_name'],
            $_SERVER['DOCUMENT_ROOT'] . '/' . rawurldecode($newDoc->getPath())
        );
        // Drop "blocked" status of original doc
        $doc = Database::getDocumentById($params['id']);
        $doc->unblock();
        $doc->save();
        $res["success"] = true;
        $res["message"] = "File uploaded";

        // Detect document by form signing
        if ($extra["send_email_to_user"] || $extra["send_email_to_admin"]) {
            if (\IsModuleInstalled("trusted.cryptoarmdocsforms")) {
                Loader::includeModule("trusted.cryptoarmdocsforms");
                Form::upload($doc, $extra);
            }
        }

        Utils::log([
            "action" => "signed",
            "docs" => $doc,
            "extra" => $params["extra"],
        ]);
        return $res;
    }

    /**
     * Unblocks one or multiple documents
     *
     * @param array $params [id]: array of document ids
     * @return array [success]: operation result status
     *               [message]: operation result message
     */
    static function unblock($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in AjaxCommand.unblock",
        ];

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No autorization';
            $res["noAuth"] = true;
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
                if (!$doc->hasParent()) {
                    $doc->setSignType(0);
                }
                $res["success"] = true;
                $res["message"] = "Some documents were unblocked";
                $doc->unblock();
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
        $res = [
            "success" => false,
            "message" => "Unknown error in AjaxCommand.remove",
        ];

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No autorization';
            $res["noAuth"] = true;
            return $res;
        }

        $ids = $params["ids"];

        if (!$ids) {
            $res["noIds"] = true;
            $res["message"] = "No ids were given";
            return $res;
        }

        $res = array_merge(
            $res,
            Utils::checkDocuments($ids, null, true)
        );

        $docsToRemove = array_merge(
            $res['docsOk']->toIdArray(),
            $res['docsFileNotFound']->toIdArray(),
            $res['docsBlocked']->toIdArray()
        );

        $res['docsFileNotFound'] = $res['docsFileNotFound']->toIdAndFilenameArray();
        $res['docsBlocked'] = $res['docsBlocked']->toIdAndFilenameArray();
        $res['docsOk'] = $res['docsOk']->toIdArray();

        if ($docsToRemove) {
            $res["success"] = true;
            $res["message"] = "Some documents were removed";
        } else {
            $res["message"] = "Nothing to remove";
        }

        foreach ($docsToRemove as $id) {
            $doc = Database::getDocumentById($id);
            $doc->remove();
            Utils::log([
                "action" => "removed",
                "docs" => $doc,
            ]);
        }

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
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.download",
        ];

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No autorization';
            $res["noAuth"] = true;
            return $res;
        }

        $ids = $params["ids"];
        if (!$ids) {
            $res["message"] = "No ids were given";
            return $res;
        }

        $docsFound = new DocumentCollection();
        $docsNotFound = [];
        $docsFileNotFound = new DocumentCollection();
        $docsNoAccess = [];

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

        $temporaryFileStorage = $_SERVER["DOCUMENT_ROOT"] . "upload/tmp/TCA-DocsTmp/";

        if (!file_exists($temporaryFileStorage)) {
            mkdir($temporaryFileStorage, 0744);
        }

        if ($docsFound->count()) {
            $filename = $params["filename"] ? $params["filename"] . ".zip" : "TCA-Docs.zip";
            $sDirTmpName = \randString(10);                        // Temporary folder name

            $sDirTmpPath = $temporaryFileStorage . "$sDirTmpName/";
            mkdir($sDirTmpPath, 0744, true);
            $archivePath = $temporaryFileStorage . "$filename";
            $archiveObject = \CBXArchive::GetArchive($archivePath);
            $archiveObject->SetOptions(["REMOVE_PATH" => $sDirTmpPath]);
            $docsFoundPaths = [];

            foreach ($docsFound->getList() as $doc) {
                if ($doc->getSignType() === DOC_SIGN_TYPE_DETACHED) {
                    $doc = Database::getDocumentById($doc->getOriginalId());
                }
                $docPath = urldecode($_SERVER['DOCUMENT_ROOT'] . $doc->getHtmlPath());
                $docName = $doc->getName();
                if (!file_exists($sDirTmpPath . $docName)) {
                    $newDocPath = $sDirTmpPath . $docName;
                    copy($docPath, $newDocPath);
                } else {
                    $filenameExploded = explode(".", $docName);
                    $fileExt = "." . (end($filenameExploded));
                    if ($fileExt == ".sig") {
                        $docNameWithoutSignExt = substr($docName, 0, -4);
                        $filenameExploded = explode(".", $docNameWithoutSignExt);
                        $fileExt = "." . (end($filenameExploded)) . ".sig";
                    }
                    $docNameWithoutExt = substr($docName, 0, -strlen($fileExt));
                    $i = 1;
                    while (file_exists($sDirTmpPath . $docNameWithoutExt . " ($i)" . $fileExt)) {
                        $i++;
                    }
                    copy($docPath, $sDirTmpPath . $docNameWithoutExt . " ($i)" . $fileExt);
                    $newDocPath = $sDirTmpPath . $docNameWithoutExt . " ($i)" . $fileExt;
                }
                $docsFoundPaths[] = $newDocPath;
            }
            $archiveObject->Pack($docsFoundPaths);

            foreach ($docsFoundPaths as $file) {
                if (is_file($file)) unlink($file);
            }

            rmdir($sDirTmpPath);
        }

        if ($docsNotFound) {
            $res["docsNotFound"] = $docsNotFound;
        }

        if ($docsFileNotFound->count()) {
            $res["docsFileNotFound"] = [];
            foreach ($docsFileNotFound->getList() as $doc) {
                $res["docsFileNotFound"][] = [
                    "filename" => $doc->getName(),
                    "id" => $doc->getId(),
                ];
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
     *                      [force]: return doc with exact id
     *                      [file]: path to file
     * @return void
     */
    static function content($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.content",
        ];
        if ($params["id"]) {
            $doc = Database::getDocumentById($params['id']);
            if ($doc) {
                if ($params["force"]) {
                    $file = $doc->getFullPath();
                } else {
                    $doc = $doc->getLastDocument();
                    $file = $doc->getFullPath();
                    if ($doc->getSignType() === DOC_SIGN_TYPE_DETACHED) {
                        $originalDoc = Database::getDocumentById($doc->getOriginalId());
                        $originalFile = $originalDoc->getFullPath();
                    }
                }
                if ($params["view"]) {
                    if ($doc->getSignType() === DOC_SIGN_TYPE_DETACHED) {
                        Utils::view($originalFile, $originalDoc->getName());
                    } else {
                        Utils::view($file, $doc->getName());
                    }
                } else {
                    // TODO: change output docs to cryptoarm gost
                    if ($doc->getSignType() === DOC_SIGN_TYPE_DETACHED) {
                        Utils::download($originalFile, $originalDoc->getName());
                    } else {
                        Utils::download($file, $doc->getName());
                    }
                }
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


    static function protocol($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.protocol",
        ];

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No autorization';
            $res["noAuth"] = true;
            return $res;
        }

        $id = $params["id"];

        if (!$id) {
            $res["message"] = "No id given";
            return $res;
        }

        $res = array_merge(
            $res,
            Utils::checkDocuments([$id], DOC_SHARE_READ, true)
        );

        $res['docsFileNotFound'] = $res['docsFileNotFound']->toIdAndFilenameArray();
        $res['docsBlocked'] = $res['docsBlocked']->toIdAndFilenameArray();

        if (!$res['docsOk']->count()) {
            $res["message"] = "Document is not found";
            return $res;
        }

        $doc = $res['docsOk']->getList()[0];

        Protocol::createProtocol($doc);
    }


    /**
     * Registers new account in licensesvc.
     *
     * @return array [success]: operation result status
     *               [message]: operation result message
     *               [data]: string ascii, account number
     */
    static function registerAccountNumber() {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.registerAccountNumber",
        ];

        $accountNumberData = License::registerAccountNumber();

        if ($accountNumberData['success']) {
            $res = [
                "success" => true,
                "data" => $accountNumberData['data'],
                "message" => "OK",
            ];
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
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.checkAccountBalance",
        ];

        $accountNumber = $params['accountNumber'];
        $balanceData = License::checkAccountBalance($accountNumber);

        if ($balanceData['success']) {
            $res = [
                "success" => true,
                "data" => $balanceData['data'],
                "message" => "OK",
            ];
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
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.activateJwtToken",
        ];

        $accountNumber = $params['accountNumber'];
        $jwt = $params['jwt'];
        $balanceData = License::activateJwtToken($accountNumber, $jwt);

        if ($balanceData['success']) {
            $res = [
                "success" => true,
                "data" => $balanceData['data'],
                "message" => "OK",
            ];
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
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.sendEmail",
        ];

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No autorization';
            $res["noAuth"] = true;
            return $res;
        }

        $ids = $params['ids'];
        $event = $params['event'];
        $arEventFields = $params['arEventFields'];
        $messageId = $params['messageId'];

        if ($event == "MAIL_EVENT_ID_TO" || $event == "MAIL_EVENT_ID_SHARE" || $event == "MAIL_EVENT_ID_REQUIRED_SIGN") {
            $doc = Database::getDocumentById($ids[0]);
            $userIdOwner = (int)$doc->getOwner();
            $userOwnerName = Utils::getUserName($userIdOwner);
            $arEventFields["FIO_FROM"] = $userOwnerName;
            $arEventFields["FIO_TO"] = Utils::getUserName(Utils::getUserIdByEmail($arEventFields["EMAIL"]));
        }

        $res = array_merge(
            $res,
            Utils::checkDocuments($ids, DOC_SHARE_READ, true)
        );

        $res['docsFileNotFound'] = $res['docsFileNotFound']->toIdAndFilenameArray();

        if (!$res['docsOk']->count()) {
            $res["message"] = "Documents not found";
            return $res;
        }

        $sendStatus = Email::sendEmail($ids, $event, $arEventFields, $messageId);

        if ($sendStatus['success']) {
            $res = [
                "success" => true,
                "message" => "Email sent successfully",
            ];
        } else {
            $res["message"] = $sendStatus["message"];
            $res["noSendMail"] = true;
        }

        return $res;
    }

    static function share($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.share",
        ];

        if (!Utils::checkAuthorization()) {
            $res["message"] = "No autorization";
            $res["noAuth"] = true;
            return $res;
        }

        $email = $params["email"];
        $level = $params["level"];
        $sendEmail = array_key_exists("sendEmail", $params) ? $params["sendEmail"] : true;

        $userId = Utils::getUserIdByEmail($email);
        if (!$userId) {
            $res["message"] = "User not found";
            $res["noUser"] = $email;
            return $res;
        }

        $ids = $params["ids"];

        $res = array_merge(
            $res,
            Utils::checkDocuments($ids, null, true)
        );

        if (!$res['docsOk']->count()) {
            $res["message"] = "Documents not found";
            return $res;
        }

        $docsToShare = array_merge(
            $res['docsOk']->toIdArray(),
            $res['docsFileNotFound']->toIdArray(),
            $res['docsBlocked']->toIdArray()
        );

        $res['docsFileNotFound'] = $res['docsFileNotFound']->toIdAndFilenameArray();
        $res['docsBlocked'] = $res['docsBlocked']->toIdAndFilenameArray();
        $res['docsOk'] = $res['docsOk']->toIdArray();

        if (!$docsToShare) {
            $res["message"] = "Nothing to share";
            return $res;
        }

        foreach ($docsToShare as $docId) {
            $doc = Database::getDocumentById($docId);
            $fileName = $doc->getName();
            $ownerId = $doc->getOwner();
            $shareFrom = Utils::getUserName($ownerId) ?: "";

            if ($sendEmail) {
                $arEventFields = [
                    "EMAIL" => $email,
                    "FILE_NAME" => $fileName,
                    "SHARE_FROM" => $shareFrom,
                    "FIO_TO" => Utils::getUserName(Utils::getUserIdByEmail($email)),
                ];

                Email::sendEmail([$docId], "MAIL_EVENT_ID_SHARE", $arEventFields, "MAIL_TEMPLATE_ID_SHARE");
            }
            $doc->share($userId, $level);
            $doc->save();
        }

        $res["success"] = true;
        $res["message"] = "Documents shared";
        return $res;
    }

    static function unshare($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.unshare",
        ];

        if (!Utils::checkAuthorization()) {
            $res["message"] = "No authorization";
            $res["noAuth"] = true;
            return $res;
        }

        $docIds = $params["docIds"];
        $userId = $params["userId"];
        if (!$userId) {
            $userId = Utils::currUserId();
        }

        if (!$docIds) {
            $res["noIds"] = true;
            $res["message"] = "No ids were given";
            return $res;
        }

        $res = array_merge(
            $res,
            Utils::checkDocuments($docIds, DOC_SHARE_READ, true)
        );

        $docsToUnshare = array_merge(
            $res['docsOk']->toIdArray(),
            $res['docsFileNotFound']->toIdArray(),
            $res['docsBlocked']->toIdArray()
        );

        $res['docsFileNotFound'] = $res['docsFileNotFound']->toIdAndFilenameArray();
        $res['docsBlocked'] = $res['docsBlocked']->toIdAndFilenameArray();
        $res['docsOk'] = $res['docsOk']->toIdArray();

        if ($docsToUnshare) {
            $res["success"] = true;
            $res["message"] = "Some documents were unshared";
        } else {
            $res["message"] = "Nothing to unshare";
        }

        foreach ($docsToUnshare as $docId) {
            $doc = Database::getDocumentById($docId);
            $docRequire = $doc->getRequires();
            $doc->unshare($userId);
            $doc->save();
            if (in_array($userId, $docRequire->getUserList())) {
                Database::removeRequireToSign($docId, $userId);
            }
        }
        return $res;
    }

    /**
     * Search for locked documents with defined token
     *
     * @param $params [blockToken]: string ascii
     *
     * @return boolean
     */
    public function blockCheck($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.blockCheck",
        ];

        if (!Utils::checkAuthorization()) {
            $res["message"] = "No authorization";
            $res["noAuth"] = true;
            return $res;
        }

        $token = $params['blockToken'];

        if (!$token) {
            $res["message"] = "No token were given";
            return $res;
        }

        $docs = Database::getDocumentsByBlockToken($token);
        if ($docs->count()) {
            $res["message"] = "Documents blocked with this token are found";
            $res["success"] = true;
        } else {
            $res["message"] = "No documents are blocked with this token";
        }

        return $res;
    }


    public function requireToSign($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.requireToSign",
        ];

        if (!Utils::checkAuthorization()) {
            $res["message"] = "No autorization";
            $res["noAuth"] = true;
            return $res;
        }

        $ids = $params["ids"];
        $usersEmail = explode(" ", $params["email"]);

        foreach ($usersEmail as $email) {
            $userId = Utils::getUserIdByEmail($email);

            if ($userId === null) {
                if (!ModuleManager::isModuleInstalled("trusted.id")) {
                    return [
                        "success" => false,
                        "message" => "Not installed trusted.id",
                    ];
                }

                Utils::registerUser($email);

                $userId = Utils::getUserIdByEmail($email);
                $newUser = true;
            } else {
                $newUser = false;
            }

            $userInfo = [
                "email" => $email,
                "userId" => $userId,
                "newUser" => $newUser,
            ];

            $usersInfo[] = $userInfo;
        }

        if (empty($usersInfo)) {
            return $res["message"] = "No user";
        }

        $params["sendEmail"] = false;
        $params["level"] = DOC_SHARE_SIGN;

        foreach ($usersEmail as $key => $email) {
            $params["email"] = $email;
            $response = self::share($params);

            if ($response["success"]) {
                $res = array_merge(
                    $res,
                    Utils::checkDocuments($ids, null, true)
                );

                if (!$res['docsOk']->count()) {
                    $res["message"] = "Documents not found";
                    return $res;
                }

                $docsToRequireSign = array_merge(
                    $res['docsOk']->toIdArray(),
                    $res['docsFileNotFound']->toIdArray(),
                    $res['docsBlocked']->toIdArray()
                );

                $res['docsFileNotFound'] = $res['docsFileNotFound']->toIdAndFilenameArray();
                $res['docsBlocked'] = $res['docsBlocked']->toIdAndFilenameArray();
                $res['docsOk'] = $res['docsOk']->toIdArray();

                if (!$docsToRequireSign) {
                    $res["message"] = "Nothing to require";
                    return $res;
                }

                foreach ($docsToRequireSign as $docId) {
                    $doc = Database::getDocumentById($docId);
                    $fileName[] = $doc->getName();
                    $ownerId = $doc->getOwner();
                }

                $requireFrom = Utils::getUserName($ownerId) ? : "";

                $arEventFields = [
                    "EMAIL" => $email,
                    "FILE_NAME" => $fileName,
                    "REQUESTING_USER" => $requireFrom,
                    "DOCS_ID" => implode(".", $ids),
                    "USER_ID" => $usersInfo[$key]["userId"],
                    "FIO_TO" => Utils::getUserName(Utils::getUserIdByEmail($email)),
                ];

                Email::sendEmail($ids, "MAIL_EVENT_ID_REQUIRED_SIGN", $arEventFields, "MAIL_TEMPLATE_ID_REQUIRED_SIGN");
            } else {
                return $response;
            }
        }

        $res = [
            "success" => true,
            "message" => "Documents required to sign"
        ];

        return $res;
    }

    /**
     * Create transaction in DB
     * @param $params ["id"] ids of documents
     *                ["method"] type of method
     * @return array info about created transaction
     */
    public function createTransaction($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.createTransaction",
        ];

        if (!Utils::checkAuthorization()) {
            $res["message"] = "No autorization";
            $res["noAuth"] = true;
            return $res;
        }

        global $USER;

        $userId = $USER->GetID();
        $ids = $params["id"];

        if (!$ids) {
            $res["message"] = "No ids were given";
            $res["noIds"] = true;
            return $res;
        }

        switch ($params["method"]) {
            case "sign":
                $method = DOC_TRANSACTION_TYPE_SIGN;
                $res = Utils::checkDocuments($ids, DOC_SHARE_SIGN, false, true);
                break;
            case "verify":
                $method = DOC_TRANSACTION_TYPE_VERIFY;
                $res = Utils::checkDocuments($ids, DOC_SHARE_READ, true, false);
                $res['docsUnsigned'] = $res['docsUnsigned']->toIdAndFilenameArray();
                break;
            default:
                $res["message"] = "Unknown method";
                return $res;
        }

        $res['docsFileNotFound'] = $res['docsFileNotFound']->toIdAndFilenameArray();
        $res['docsBlocked'] = $res['docsBlocked']->toIdAndFilenameArray();

        if ($res['docsOk']->count()) {
            $res['docsOk'] = $res['docsOk']->toJSON();
        } else {
            $res['docsOk'] = null;
        }

        if ($res['docsOk']) {
            $res["success"] = true;
            $res["message"] = "Some documents were sent for " . $params["method"];
        } else {
            $res["message"] = "Nothing to " . $params["method"];
            return $res;
        }

        if ($transactionInfo = Database::insertTransaction($ids, $userId, $method)) {
            $res["success"] = true;
            $res["uuid"] = $transactionInfo;
            return $res;
        }

        return $res;
    }

    public function generateJson($UUID) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.generateJson",
        ];

        $deauthorize = false;

        $transactionInfo = Database::getTransaction($UUID);

        if (!$transactionInfo) {
            $res["message"] = "UUID is does not exist";
            return $res;
        }

        $userId = $transactionInfo["USER_ID"];
        $docsId = $transactionInfo["DOCUMENTS_ID"];
        $transactionStatus = $transactionInfo["TRANSACTION_STATUS"];
        $transactionType = $transactionInfo["TRANSACTION_TYPE"];

        if ($transactionStatus) {
            $res["message"] = "accessToken is already used";
            return $res;
        }

        Database::stopTransaction($UUID);

        if (!Utils::checkAuthorization()) {
            global $USER;
            $USER->Authorize($userId);
            $deauthorize = true;
        }

        switch ($transactionType) {
            case DOC_TRANSACTION_TYPE_SIGN:
                $response = self::sign(["id" => $docsId]);
                $JSON->method = "sign";
                $extra->token = $response["token"];
                $extra->signType = TR_CA_DOCS_TYPE_SIGN;
                $params->extra = $extra;
                break;
            case DOC_TRANSACTION_TYPE_VERIFY:
                $response = self::verify(["id" => $docsId]);
                $JSON->method = "verify";
                break;
            default:
                $res["message"] = "Unknown transaction type";
                return $res;
        }

        if (empty($response["docsOk"])) {
            $res["message"] = "Documents not found";
            return $res;
        }

        $JSON->jsonrpc = "2.0";
        $params->files = json_decode($response["docsOk"]);
        $params->license = $response["license"];
        $params->uploader = TR_CA_DOCS_AJAX_CONTROLLER . '?command=upload';
        $JSON->params = $params;

        if ($deauthorize) {
            $USER->Logout();
        }

        header("Content-type: text/plain");
        header("Content-Disposition: attachment; filename=someFile.json");
        echo json_encode($JSON);
        die;
    }
}

