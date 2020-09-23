<?php

namespace Trusted\CryptoARM\Docs;

use Bitrix\Main\Loader;
use DateTime;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

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
            Utils::checkDocuments($ids, DOC_SHARE_SIGN, false, true, null, true)
        );

        $res["token"] = $params["UUID"];
        $res["signType"] = TR_CA_DOCS_TYPE_SIGN;

        foreach ($res['docsOk']->getList() as $okDoc) {
            $okDoc->setSignType(TR_CA_DOCS_TYPE_SIGN);
            $okDoc->block($res["token"]);
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
        } else {
            $res['license'] = null;
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

        if (!count($params)) {
            $res["message"] = "No ids documents";
            return $res;
        }

        $id = $params["params"]["id"];
        if ($id) {
            $transaction = Database::getTransaction($params["params"]["id"]);
            $extra = json_decode($transaction["EXTRA"], true);
        }

        if ($params["params"]["status"] == "Canceled") {
            foreach ($transaction["DOCUMENTS_ID"] as $idDoc) {
                $doc = Database::getDocumentById($idDoc);
                $doc->unblock();
                $doc->save();
            }
            $res["message"] = "Transaction canceled";
            return $res;
        }

        foreach ($params["params"]["directResults"] as $arResult) {

            $idDoc = $arResult["id"];
            $doc = Database::getDocumentById($idDoc);
            $lastDoc = $doc->getLastDocument();

            if (DOC_TYPE_SIGNED_FILE === 1) {
                if ($lastDoc) {
                    $doc = $lastDoc;
                    $idDoc = $doc->getId();
                }
            }

            if (!$doc) {
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
            if ($doc->getBlockToken() !== $id) {
                $res["message"] = "Wrong token";
                return $res;
            }

            $doc->setSignType(TR_CA_DOCS_TYPE_SIGN);
            $doc->save();
            $newDoc = $doc->copy();
            $signatures = json_encode($arResult["signers"]);
            $newDoc->setSignatures($signatures);
            // Append new user to the list of signers
            $newDoc->addSigner($doc->getBlockBy());
            $newDoc->setType(DOC_TYPE_SIGNED_FILE);
            $newDoc->setParent($doc);
            //Detect document by order signing
            if ($extra["role"]) {
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

            $content = base64_decode($arResult["out"]);
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . rawurldecode($newDoc->getPath()), $content);

            $newDoc->setHash(hash_file('md5',$_SERVER['DOCUMENT_ROOT'] . '/' . rawurldecode($newDoc->getPath())));
            $newDoc->setSignType(TR_CA_DOCS_TYPE_SIGN);
            $newDoc->save();

            // Drop "blocked" status of original doc
            $doc = Database::getDocumentById($idDoc);
            $doc->unblock();
            $doc->save();
            $res["success"] = true;
            $res["message"] = "File uploaded";

            //Detect document by form signing
            if ($extra["send_email_to_user"] || $extra["send_email_to_admin"]) {
                if (\IsModuleInstalled("trusted.cryptoarmdocsforms")) {
                    Loader::includeModule("trusted.cryptoarmdocsforms");
                    Form::upload($doc, $extra);
                }
            }
        }
        Utils::log([
            "action" => "signed",
            "docs" => $doc,
            "extra" => $extra,
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

        $wf = ModuleManager::isModuleInstalled("trusted.cryptoarmdocsbp");

        $ids = $params["ids"];

        if (!$ids) {
            $res["noIds"] = true;
            $res["message"] = "No ids were given";
            return $res;
        }

        if($wf) {
            $docsInWF = Database::getDocumentIdsInWorkflows();
            foreach ($ids as $id) {
                if (in_array($id, $docsInWF)) {
                    $wfs[] = $id;
                    $res['docsInWF'][] = [
                        "id" => $id,
                        "name" => (Database::getDocumentById($id)->getName()),
                    ];
                    $res['WFDocs'] = true;
                }
            }
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
            if (!($wf && in_array($id, $wfs))){
                $doc = Database::getDocumentById($id);
                $doc->remove();
                Utils::log([
                    "action" => "removed",
                    "docs" => $doc,
                ]);
            };
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

        if (!is_array($ids)){
            $ids = json_decode($ids);
        }
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

        if ($count = $docsFound->count()) {
            $filename = $params["filename"] ? $params["filename"] . ".zip" : "TCA-Docs.zip";
            $sDirTmpName = \randString(10);                        // Temporary folder name

            $sDirTmpPath = $temporaryFileStorage . "$sDirTmpName/";
            mkdir($sDirTmpPath, 0744, true);
            $archivePath = $temporaryFileStorage . "$filename";
            $archiveObject = \CBXArchive::GetArchive($archivePath);
            $archiveObject->SetOptions(["REMOVE_PATH" => $sDirTmpPath]);
            $docsFoundPaths = [];

            foreach ($docsFound->getList() as $doc) {
                $detachedSign = null;
                if ($doc->getSignType() === DOC_SIGN_TYPE_DETACHED) {
                    $detachedSign = $doc;
                    $doc = Database::getDocumentById($doc->getOriginalId());
                } else {
                    $protocolFlage =json_decode($params["protocol"], true);
                    if ($count === 1 && !$protocolFlage) {
                        return self::content(["id" => $doc->getId()]);
                    }
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
                if ($detachedSign){
                    $docPath = urldecode($_SERVER['DOCUMENT_ROOT'] . $detachedSign->getHtmlPath());
                    $docName = $detachedSign->getName();
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
                if ($params["protocol"]) {
                    ob_start();
                    $paramsProtocol = [
                        "id" => $doc->getId(),
                        "protocol" => true
                    ];
                    AjaxCommand::protocol($paramsProtocol);
                    $pdf_content=ob_get_contents();
                    ob_clean();

                    $protocolId = \CFile::SaveFile(array(
                        'name' => $doc->getName() . '_protocol.pdf',
                        'size' => strlen($pdf_content),
                        'type' => 'application/pdf',
                        'content' => $pdf_content,
                        ),
                        'tmp'
                    );

                    $protocolIds[] = $protocolId;
                    $pathProtocol = $_SERVER['DOCUMENT_ROOT'] . \CFile::GetPath($protocolId);
                    $newDocPath = $sDirTmpPath . $doc->getName() . '_protocol.pdf';
                    copy($pathProtocol, $newDocPath);

                    $docsFoundPaths[] = $newDocPath;
                }
            }
            $archiveObject->Pack($docsFoundPaths);

            foreach ($docsFoundPaths as $file) {
                if (is_file($file)) unlink($file);
            }

            foreach ($protocolIds as $protocolId) {
                if (($protocolId)) \CFile::Delete($protocolId);
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

        if ($params["force"]) {
            Utils::download($_SERVER["DOCUMENT_ROOT"] . "/upload/tmp/TCA-DocsTmp/" . $filename, $filename);
            die();
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

        $token = $params["accessToken"];
        $userId = null;

        if (Utils::checkAuthorization()) {
            $userId = Utils::currUserId();
        }

        if ($token) {
            $transactionInfo = Database::getTransaction($token);
            if (!$transactionInfo) {
                $res["message"] = "Transaction does not exist";
                return $res;
            }
            $userId = $transactionInfo["USER_ID"];
            // if ($transactionInfo["TRANSACTION_TYPE"] === DOC_TRANSACTION_TYPE_VERIFY) {
            //     //Database::removeTransaction($token);
            // }
        }

        if (!$userId) {
            $res["message"] = "No authorization or no token";
            return $res;
        }

        if ($params["id"]) {
            $doc = Database::getDocumentById($params['id']);
            if ($doc) {
                if (!($doc->getOwner() == $userId || $doc->accessCheck($userId, DOC_SHARE_READ))) {
                    $res["message"] = "No access";
                    return $res;
                }
                if ($params["force"]) {
                    $file = $doc->getFullPath();
                } elseif ($params["detachedSign"]) {
                    $doc = $doc->getLastDocument();
                    $file = $doc->getFullPath();
                } else {
                    if ($doc->getSignType() === DOC_SIGN_TYPE_DETACHED) {
                        $doc = Database::getDocumentById($doc->getOriginalId());
                    } else {
                        $doc = $doc->getLastDocument();
                    }
                    $file = $doc->getFullPath();
                }
                if ($params["view"]) {
                    Utils::view($file, $doc->getName());
                } else {
                    Utils::download($file, $doc->getName());
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

        $saveProtocol = $params["protocol"];
        if ($saveProtocol) {
            Protocol::createProtocol($doc, true);
        } else {
            Protocol::createProtocol($doc, false);
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
        if ($userId == Utils::currUserId()) {
            $res["message"] = "User is owner";
            $res["IsOwner"] = true;
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
            if ($doc->accessCheck($userId, DOC_SHARE_READ)) {
                $res["message"] = "User already have access";
                $res["HaveAccess"] = true;
            }

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
                $uuid = $docRequire->getUuidTransactionByUserId($userId);
                Database::stopTransaction($uuid);
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

        $tokens = $params['blockTokens'];

        if (!$tokens) {
            $res["message"] = "No token were given";
            return $res;
        }

        $collection = new Collection();

        foreach ($tokens as $token) {
            $docs = Database::getDocumentsByBlockToken($token);
            $collection = Collection::mergeCollections($collection, $docs);
        }

        if ($collection->count()) {
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
        $usersEmail = $params["email"];

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
                    Utils::checkDocuments($ids, null, true, null, true)
                );

                if (!($res['docsOk']->count() || $res['docsUnsigned']->count())) {
                    $res["message"] = "Documents not found";
                    return $res;
                }

                $docsToRequireSign = array_merge(
                    $res['docsOk']->toIdArray(),
                    $res['docsFileNotFound']->toIdArray(),
                    $res['docsBlocked']->toIdArray(),
                    $res['docsUnsigned']->toIdArray()
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

                $transactionInfo = self::createTransaction(["id" => $ids, "method" => "sign", "userId" => $usersInfo[$key]["userId"]]);

                if (!$transactionInfo["success"]) {
                    $res["message"] = $transactionInfo["message"];
                    return $res;
                }

                $UUID = $transactionInfo["uuid"];
                $redirectUrl = "https://" . TR_CA_HOST . "/bitrix/components/trusted/docs/authForSign.php?accessToken=" . $UUID;

                $arEventFields = [
                    "EMAIL" => $email,
                    "FILE_NAME" => $fileName,
                    "REQUESTING_USER" => $requireFrom,
                    "DOCS_ID" => implode(".", $ids),
                    "USER_ID" => $usersInfo[$key]["userId"],
                    "FIO_TO" => Utils::getUserName(Utils::getUserIdByEmail($email)),
                    "SIGN_URL" => $redirectUrl,
                    "TRANSACTION_UUID" => $UUID,
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

        if (is_null($params["userId"])) {
            global $USER;
            $userId = $USER->GetID();
        } else {
            $userId = $params["userId"];
        }

        $ids = $params["id"];

        if (!$ids) {
            $res["message"] = "No ids were given";
            $res["noIds"] = true;
            return $res;
        }

        switch ($params["method"]) {
            case "sign":
                $method = DOC_TRANSACTION_TYPE_SIGN;
                $res = Utils::checkDocuments($ids, DOC_SHARE_SIGN, false, true, null, true );
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

        $extra = json_encode($params["extra"]);

        if ($transactionInfo = Database::insertTransaction($ids, $userId, $method, $extra)) {
            $res["success"] = true;
            $res["uuid"] = $transactionInfo;
            return $res;
        }

        return $res;
    }

    public function generateJson($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.generateJson",
        ];

        $deauthorize = false;

        $UUID = $params["id"];

        if (!$UUID) {
            $res["message"] = "accessToken is not find in params";
            return $res;
        }

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

        $JSON = new class {};
        $result = new class {};
        $props = new class {};
        $extra = new class {};

        switch ($transactionType) {
            case DOC_TRANSACTION_TYPE_SIGN:
                $response = self::sign(["id" => $docsId, "UUID" => $UUID]);
                $result->operation = ["SIGN"];
                $extra->token = $response["token"];
                $extra->signType = TR_CA_DOCS_TYPE_SIGN;
                $extra->signStandard = TR_CA_DOCS_SIGN_STANDARD;
                $props->extra = $extra;
                break;
            case DOC_TRANSACTION_TYPE_VERIFY:
                $response = self::verify(["id" => $docsId]);
                $result->operation  = ["VERIFYSIGN"];
                $extra->token = $UUID;
                $props->extra = $extra;
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
        $props->files = json_decode($response["docsOk"]);
        $props->license = $response["license"];
        $result->props = $props;
        $JSON->result = $result;

        if ($deauthorize) {
            $USER->Logout();
        }

        header("Content-type: text/plain");
        header("Content-Disposition: attachment; filename=someFile.json");
        echo json_encode($JSON);
        die;
    }

    public function getTransactionUrlByToken($params) {
        $url = "cryptoarm://sign/" . TR_CA_DOCS_AJAX_CONTROLLER . "?command=JSON&accessToken=" . $params["accessToken"];

        header("Location: " . $url);
        die();
        /*$res = [
            "success" => false,
            "message" => "Unknown error in Ajax.getTransactionUrlByToken",
        ];

        $UUID = $params["accessToken"];

        if (!$UUID) {
            $res["message"] = "accessToken is not find in params";
            return $res;
        }

        $transactionInfo = Database::getTransaction($UUID);

        if (!$transactionInfo) {
            $res["message"] = "UUID is does not exist";
            return $res;
        }

        $transactionStatus = $transactionInfo["TRANSACTION_STATUS"];
        $transactionType = $transactionInfo["TRANSACTION_TYPE"];

        if ($transactionStatus) {
            $res["message"] = "accessToken is already used";
            return $res;
        }

        $url = "cryptoarm://";

        switch ($transactionType) {
            case DOC_TRANSACTION_TYPE_SIGN:
                $url .= "sign";
                break;
            case DOC_TRANSACTION_TYPE_VERIFY:
                $url .= "verify";
                break;
            default:
                $res["message"] = "Unknown method";
                return $res;
        }

        $url .= "/" . TR_CA_DOCS_AJAX_CONTROLLER . "?command=JSON&accessToken=" . $UUID;

        header("Location: " . $url);
        exit();*/
    }

    public function getInfoForModalWindow($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.getInfoForModalWindow",
        ];

        $id = $params["id"];

        if (!$id) {
            $res["message"] = "id is not find in params";
            return $res;
        }

        if (!Utils::checkAuthorization()) {
            $res["message"] = "No authorization";
            $res["noAuth"] = true;
            return $res;
        }

        $doc = Database::getDocumentById($id);

        if ($doc) {
            $currUserId = Utils::currUserId();
            if ($doc->getOwner() == $currUserId) {
                $accessLevel = "OWNER";
            } elseif ($doc->accessCheck($currUserId, DOC_SHARE_SIGN)) {
                $accessLevel = "SIGN";
            } elseif ($doc->accessCheck($currUserId, DOC_SHARE_READ)) {
                $accessLevel = "READ";
            }

            $docObject = Database::getDocumentById($doc->getId());
            $docRequire = $docObject->getRequires();

            $userIds = Database::getUserIdsByDocument($doc->getId());
            $status = [];
            $signersString = $doc->getSigners();
            preg_match_all('!\d+!', $signersString, $signersArray);

            foreach ($userIds as $id) {
                if ($doc->accessCheck($id, DOC_SHARE_SIGN)) {
                    $sharedAccessLevel = "SIGN";
                } elseif ($doc->accessCheck($id, DOC_SHARE_READ)) {
                    $sharedAccessLevel = "READ";
                }
                if (in_array($id, $docRequire->getUserList())) {
                    $sharedMustToSign = !$docRequire->getSignStatusByUser($id);
                } else {
                    $sharedMustToSign = false;
                }
                $status[] = array(
                    'id' => $id,
                    'name' => Utils::getUserName($id),
                    'access_level' => $sharedAccessLevel,
                    'signed' => in_array($id, $signersArray[0]) ? true : false,
                    'mustToSign' => $sharedMustToSign,
                );
            }

            $data = [
                "docname" => $doc->getName(),
                "sharedstatus" => $status,
                "currentuseraccess" => $accessLevel
            ];

            return [
                "success" => true,
                "message" => "ok",
                "data" => $data,
            ];
        } else {
            $res["message"] = "Document is not found";
            return $res;
        }

    }

    /**
     * @param array $params [typeOfMessage]
     *                      [page]
     *                      [count]
     */

    static function getMessageList($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in AjaxCommand.getMessageList",
        ];

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            return $res;
        }

        if(!$params["typeOfMessage"]) {
            $res['message'] = 'Get no type';
            return $res;
        }

        if ($params["page"] && $params["count"] ) {
            $args['firstElem'] = $params["page"] * $params["count"];
            $args['count'] = $params['count'];
        }

        $args["userId"] = Utils::currUserId();

        $args['typeOfMessage'] = $params['typeOfMessage'];
        switch ($args["typeOfMessage"]) {
            case "incoming":
                $mesIds = Messages::getIncomingMessages($args);
                break;
            case "outgoing":
            case "drafts":
                $mesIds = Messages::getOutgoingMessages($args);
                break;
            default:
                $res["message"] = "Unknown type";
                return $res;
        }
        if ($mesIds) {
            $res['messages'] = [];
            foreach ($mesIds as $mesId) {
                $message = Messages::getMessageInfo($mesId);
                $message["id"] = $mesId;
                if ($message["sender"])
                    $message["sender"] = Utils::getUserEmail($message["sender"]);

                if ($message["recepient"])
                    $message["recepient"] = Utils::getUserEmail($message["recepient"]);

                if ($message["docs"]) {
                    $docs = [];
                    foreach ($message["docs"] as $docId) {
                        $docName = Database::getDocumentById($docId)->getName();
                        $docs[]  = [
                            "id" => $docId,
                            "name" => $docName
                        ];
                    }
                    $message["docs"] = $docs;
                }

                $dateCreated = strtotime($message["time"]);
                if (date("d.m.o", $dateCreated) == date("d.m.o", time())) {
                    $dateCreated =  date("H:i", $dateCreated);
                } else {
                    $dateCreated =  date("d.m.o", $dateCreated);
                }
                $message["time"] = $dateCreated;

                $res['messages'][] = $message;
            }
            $res['success'] = true;
            $res['message'] = 'Success';
        } else {
            $res['message'] = 'No messages';
            $res['noMess'] = true;
        }
        return $res;
    }

    /**
     * Writes all the message's data into the database
     *
     * @param array $params [docsIds]: array of doc Ids in message
     *                      [fields]: array of message's fields
     *                              []
     *                              []
     *                      [send]: if false - message in draft
     */

    static function newMessage($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in AjaxCommand.createMessage",
        ];

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            $res['noAith'] = 'true';
        };

        if(!$params['docsIds']) {
            $res['message'] = 'Nothing to send';
            return $res;
        };

        $params['recepientEmail'] = trim($params['recepientEmail']);

        if ($params['send'] == "true"){
            if ($params['recepientEmail'] == null) {
                $res['message'] = 'No Email';
                return $res;
            };

            $userId = Utils::getUserIdByEmail($params['recepientEmail']);
            if (!$userId) {
                $res['message'] = 'User not exists';
                return $res;
            }
        } else {
            if ($params['recepientEmail'] != null) {
                $userId = Utils::getUserIdByEmail($params['recepientEmail']);
                if (!$userId) {
                    $res['message'] = 'User not exists';
                    return $res;
                }
            }
        }

        $params['recepientId'] = $userId;

        $draft = Messages::createDraft($params);
        $res['messId'] = $draft;
        $res['message'] = "Draft is created";

        if($params['send'] == "true") {
            $par['messId'] = $draft;
            Messages::sendMessage($par);
            $res['message'] = "Message sent";
        }

        $res['success'] = true;
        return $res;
    }
    /**
     * returns information about message
     * @param array
     */
    static function getMessageInfo($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in AjaxCommand.getMessageInfo",
        ];

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            $res['noAuth'] = true;
            return $res;
        }

        $res["messageInfo"] = Messages::getMessageInfo($params['id']);
        $res["messageInfo"]["sender"] = Utils::getUserEmail($params['messageInfo']['sender']);
        if ($res["messageInfo"]["recepient"] != null) {
            $res["messageInfo"]["recepient"] = Utils::getUserEmail($params['messageInfo']['recepient']);
        }
        $res["success"] = true;
        $res["message"] = "Success";
        return $res;
    }

    static function sendCancel($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in AjaxCommand.sendCancel",
        ];

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            $res['noAuth'] = true;
            return $res;
        } else {
            $userId = Utils::currUserId();
            if (!$params["messId"]) {
                $res['message'] = 'Nothing to remove';
                return $res; 
            }
            if (!Messages::isMessageExists($params["messId"])) {
                $res['message'] = 'Message not exists';
                return $res;
            }
            if (!($userId == Messages::getSenderId($params['messId']))) {
                $res['message'] = 'Not a sender try to cancel';
                return $res;
            }
        }

        $messId = $params['messId'];
        $success = Messages::sendCancelInDB($params);
        if ($success) {
            $res['message'] = 'Message sending is cancelled';
            $res['success'] = true;
        } else {
            $res['message'] = 'Cannot cancel';
            $res['success'] = false;
            $res['toLate'] = true;
            return $res;
        }
    }

    /**
     * @param array $params [searchKey]
     *                      [typeOfMessage]
     */

    static function searchMessage($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in AjaxCommand.searchMessage",
        ];

        if(!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            $res['noAuth'] = true;
            return $res;
        } else {
            $params['userId'] = Utils::currUserId();
        }

        if (!$params['searchKey']) {
            $res['message'] = 'Nothing to search';
            return $res;
        }

        if (!$params['typeOfMessage']) {
            $res['message'] = 'Nowhere to search';
            return $res;
        }

        $mesIds = [];
        $searchKeys = explode(',', $params['searchKey']);
        foreach($searchKeys as $key) {
            $params['searchKey'] = trim($key, '+');
            $mesIds = array_merge($mesIds, Messages::searchMessage($params));
        }
        $mesIds = array_unique($mesIds);
        $res['ids'] = $mesIds;
        if (count($mesIds) != 0) {
            $res['message'] = 'Found some';
            $res['founded'] = true;
            $res['messages'] = [];
            foreach ($mesIds as $mesId) {
                $message = Messages::getMessageInfo($mesId);
                $message["id"] = $mesId;
                if ($message["sender"])
                    $message["sender"] = Utils::getUserEmail($message["sender"]);

                if ($message["recepient"])
                    $message["recepient"] = Utils::getUserEmail($message["recepient"]);

                if ($message["docs"]) {
                    $docs = [];
                    foreach ($message["docs"] as $docId) {
                        $docName = Database::getDocumentById($docId)->getName();
                        $docs[]  = [
                            "id" => $docId,
                            "name" => $docName
                        ];
                    }
                    $message["docs"] = $docs;
                }

                $dateCreated = strtotime($message["time"]);
                if (date("d.m.o", $dateCreated) == date("d.m.o", time())) {
                    $dateCreated =  date("H:i", $dateCreated);
                } else {
                    $dateCreated =  date("d.m.o", $dateCreated);
                }
                $message["time"] = $dateCreated;

                $res['messages'][] = $message;

            }
        } else {
            $res['message'] = 'Found nothing';
            $res['noMess'] = true;
        }
        $res['success'] = true;
        return $res;
    }

    /**
     * @param array $params [messId]: id of draft
     *                      [filelds]: array of changes
     *                      [send]: if true - send draft
     *
     */

    static function changeDraft($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in AjaxCommand.verify",
        ];

        if(!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            $res['noAuth'] = true;
            return $res;
        }

        if($params['fields']) {
            $args['docId'] = $params['docsIds'];
            $args['recepientId'] = $params['fields']['recepientId'];
            $args['theme'] = $params['fields']['theme'];
            $args['comment'] = $params['fields']['comment'];
            $draft = Messages::updateDraft($args);
            $res['message'] = "Draft succesfully updated";
        }

        if($res['send']) {
            Messages::sendMessage($draft);
            $res['message'] = "Message succesfully sent";
        }

        return $res;
    }

    /**
     * @param array $params [props]: array of docs properties
     *
     *
     * @return array [success]: operation result status
     *               [message]: operation result message
     */

    static function uploadFile($params) {
        $res = [
            "success" => false,
            "message" =>"Unknown error in AjaxCommand.uploadFile",
        ];

        $ar = json_decode($params["props"], true);

        if (!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            $res['noAuth'] = true;
            return $res;
        }

        $DOCUMENTS_DIR = Option::get(TR_CA_DOCS_MODULE_ID, 'DOCUMENTS_DIR', '/docs/');

        if (empty($_FILES['file']['name'])) {
            $res['message'] = 'Nothing to download';
        }

        if ($_FILES['file']['error'] != 0) {
            $res['message'] = 'File error';
            $res['fileError'] = true;
            $res['errorCode'] = $_FILES['file']['error'];
            return $res;
        }

        if ($_FILES['file']['size'] == 0) {
            $res['emptyFile'] = true;
            $res['message'] = 'Empty file';
            return $res;
        }

        $checkname = preg_replace('/[^a-zA-Z' . Loc::getMessage("TR_CA_DOCS_CYR") . '0-9_ (){}[]\.-]/u', '', $_FILES['file']['name']);
        if ($checkname != $_FILES['file']['name']) {
            $res['nameError'] = true;
            $res['message'] = 'Unacceptable name';
            return $res;
        }

        $uniqid = (string)uniqid();
        $newDocDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/';
        mkdir($newDocDir);
        $newDocFilename = Utils::mb_basename($_FILES['file']['name']);
        $newDocFilename = preg_replace('/[\s]+/u', '_', $newDocFilename);
        $newDocFilename = preg_replace('/[^a-zA-Z' . Loc::getMessage("TR_CA_DOCS_CYR") . '0-9_\.-]/u', '', $newDocFilename);
        $absolutePath = $newDocDir . $newDocFilename;
        $relativePath = $DOCUMENTS_DIR . $uniqid . '/' . $newDocFilename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $absolutePath)) {
            $props = new PropertyCollection();

            foreach ($ar as $prop) {
                $props->add(new Property((string)$prop[0], (string)$prop[1]));
            }

            $doc = Utils::createDocument($relativePath, $props);
        }

        unset($_FILES['file']['name']);

        $res["message"] = "Document is uploaded";
        $res["doc"] = $doc->getId();
        $res["success"] = true;

        return $res;
    }

    public function deleteDraft($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.deleteDraft",
        ];

        if(!Utils::checkAuthorization()) {
            $res['message'] = 'No authorization';
            return $res;
        } else {
            $userId = Utils::currUserId();
        }

        $senderId = Messages::getSenderId($params['draftId']);
        if($userId != $senderId) {
            $res['message'] = 'Not a sender';
            return $res;
        }

        Messages::deleteDraft($params);
    }

    public function getInfoDoc($params) {
        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.getInfoForModalWindow",
        ];

        $id = $params["id"];

        if (!$id) {
            $res["message"] = "id is not find in params";
            return $res;
        }

        if (!Utils::checkAuthorization()) {
            $res["message"] = "No authorization";
            $res["noAuth"] = true;
            return $res;
        }

        $doc = Database::getDocumentById($id);

        if ($doc) {

            $messageId = Messages::getMessagesByDocument($doc->getFirstParent()->getId());

            if (count($messageId) > 0) {
                $message = Messages::getMessageInfo($messageId);

                $data = [
                    "docname" => $doc->getName(),
                    "docsize" => Utils::fileSizeConvert(filesize($doc->getFullPath())),
                    "docowner" =>  Utils::getUserName($doc->getOwner()),
                    "doctype" => $doc->getType(),
                    "messageTheme" => $message["theme"],
                    "messageContent" => $message["comment"],
                    "messageAuthor" => Utils::getUserName($message["sender"]),
                    "message" => true,
                ];
            } else {
                $data = [
                    "docname" => $doc->getName(),
                    "docsize" => Utils::fileSizeConvert(filesize($doc->getFullPath())),
                    "docowner" =>  Utils::getUserName($doc->getOwner()),
                    "doctype" => $doc->getType(),
                    "message" => false,
                ];
            }

            return [
                "success" => true,
                "message" => "ok",
                "data" => $data,
            ];
        } else {
            $res["message"] = "Document is not found";
            return $res;
        }

    }

    public function getDocList($params) {

        $res = [
            "success" => false,
            "message" => "Unknown error in Ajax.getInfoForModalWindow",
        ];

        if (!Utils::checkAuthorization()) {
            $res["message"] = "No authorization";
            $res["noAuth"] = true;
            return $res;
        }

        $shared = $params["shared"];

        $page = $params["page"];

        $count = $params["count"];
        if (!$count) {
            $res["message"] = "count is not find in params";
            return $res;
        }

        $currUserId = Utils::currUserId();
        $docs = Database::getDocumentsByUser($currUserId, $shared, $page, $count);

        if ($docs) {
            foreach ($docs->getList() as $doc) {

                if ($doc->getOwner() == $currUserId && $shared)
                    continue;

                $docProps["id"] = $doc->getId();
                $docProps["name"] = $doc->getName();
                if ($doc->getOwner() == $currUserId){
                    $docProps["owner"] = true;
                } else {
                    $docProps["owner"] = Utils::getUserName($doc->getOwner());
                }


                $dateCreated = strtotime(Database::getDocumentById($doc->getId())->getCreated());
                if (date("d.m.o", $dateCreated) == date("d.m.o", time())) {
                    $dateCreated =  date("H:i", $dateCreated);
                } else {
                    $dateCreated =  date("d.m.o", $dateCreated);
                }

                $docProps["dateCreated"] = $dateCreated;
                $docsProps[] = $docProps;
            }

            return [
                "success" => true,
                "message" => "ok",
                "data" => $docsProps,
            ];
        } else {
            $res["message"] = "Document is not found";
            return $res;
        }

    }
}