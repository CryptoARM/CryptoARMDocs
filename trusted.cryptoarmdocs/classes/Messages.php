<?php

namespace Trusted\CryptoARM\Docs;

use Bitrix\Main\Loader;

//checks the name of currently installed core from highest possible version to lowest
$coreIds = [
    'trusted.cryptoarmdocscrp',
    'trusted.cryptoarmdocsbusiness',
    'trusted.cryptoarmdocsstart',
];
foreach ($coreIds as $coreId) {
    $corePathDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $coreId . "/";
    if (file_exists($corePathDir)) {
        $module_id = $coreId;
        break;
    }
}

class Messages {

    static function getOutgoingMessages($args) {
        // global $DB;
        // $sql = "SELECT  

        //         FROM " . DB_TABLE_PROPERTY . "
        //         WHERE ";
        global $DB;
        $sql = "SELECT ID FROM " . DB_TABLE_MESSAGES . " WHERE SENDER_ID=" . $args["userId"] . ' AND ';
        if($args['typeOfMessage'] == 'drafts')
        {
            $sql .= 'MES_STATUS = "DRAFT" ';
        } else {
            $sql .= 'MES_STATUS <> "DRAFT"'; 
        }
        if ($args["firstElem"] && $args["count"]) {
            $sql .= ' LIMIT ' . $args["firstElem"] . ', ' . $args["count"];
        }
        $rows = $DB->Query($sql);
        $messages = [];
        while ($row = $rows->Fetch()) {
            $messages[] = $row["ID"];
        };
        return $messages;
    }

    static function getIncomingMessages($args) {
        // global $DB;
        // $sql = "SELECT DISTINCT
        //             mes.MESSAGE_ID as message
        //         FROM 
        //             (SELECT 
        //                 prop.DOCUMENT_ID as docs
        //             FROM
        //                 " . DB_TABLE_PROPERTY . " as prop
        //                 WHERE VALUE=" . $userId . " AND (TYPE='SHARE_READ' OR TYPE='SHARE_SIGN'))
        //             RIGHT JOIN " . DB_TABLE_MESSAGES_PROPERTY . " as mes
        //                 WHERE docs=mes.DOCUMENT_ID";
        // $rows = $DB->Query($sql);
        // $messageIDS = array();
        // while ($row = $rows->Fetch()) {
        //     $messageIDS[] = $row["message"];
        // }
        // return $messageIDS;
        global $DB;
        $sql = "SELECT ID FROM " . DB_TABLE_MESSAGES . " WHERE RECEPIENT_ID=" . $args['userId'] . ' AND  MES_STATUS <> "DRAFT"';
        if ($args["firstElem"] && $args["count"]) {
            $sql .= ' LIMIT ' . $args["firstElem"] . ', ' . $args["count"];
        }
        $rows = $DB->Query($sql);
        $messages = [];
        while ($row = $rows->Fetch()) {
            $messages[] = $row["ID"];
        };
        return $messages;
    }

    static function getDocumentsInMessage($messId) {
        global $DB;
        $sql = "SELECT  
                    TDP.DOCUMENT_ID as ID
                FROM " . DB_TABLE_MESSAGES_PROPERTY . " as TDMP 
                LEFT JOIN (SELECT * FROM" . DB_TABLE_PROPERTY . " as TDP 
                    WHERE
                        (TDP.VALUE='SHARE_READ' OR TDP.VALUE='SHARE_SIGN') AND TDP.ID=TDMP.PROP_ID)
                WHERE 
                    TDMP=" . $messId;
        $rows = $DB->Query($sql);
        return $rows["ID"];
    }

    static function getSenderId($messId) {
        // $docsId = getDocumentsInMessage($messId);
        // foreach ($docsId as $docId) {
            // $email = getUserByDoc($docId);
            // return $email;
        // }
        global $DB;
        $sql = "SELECT SENDER_ID FROM " . DB_TABLE_MESSAGES . " WHERE ID=" . $messId;
        $rows = $DB->Query($sql);
        while ($row = $rows->Fetch())
            $senderId = $row["SENDER_ID"];
        return $senderId;
    }
    
    static function getRecepientId($messId) {
        global $DB;
        // $docsId = getDocumentsInMessage($messId);
        // $sql = "SELECT
                    // -- USERS.EMAIL as EMAIL
                // -- FROM
                    // -- tr_ca_docs_property as DOCS
                    // -- INNER JOIN
                    // -- b_user as USERS
                        // -- ON DOCS.VALUE = USERS.ID
                // -- WHERE DOCS.DOCUMENT_ID = '$docsId' AND
                // -- (DOCS.TYPE = 'SHARE_READ' OR DOCS.TYPE = 'SHARE_SIGN')";
        // $rows = $DB->Query($sql);
        // while ($row = $rows->Fetch()) {
            // $res = $row["EMAIL"];
            // return $res;
        // }
        $sql = "SELECT RECEPIENT_ID FROM " . DB_TABLE_MESSAGES . " WHERE ID=" . $messId;
        $rows = $DB->Query($sql);
        while ($row = $rows->Fetch()) {
            $recepientId = $row["RECEPIENT_ID"];
        }
        return $recepientId;
    }

    static function createLabel($params) {
        global $DB;
        $userId = Utils::currUserId();
        $sql = "
            INSERT INTO " . DB_TABLE_LABELS . " (USER_ID, TEXT, STYLE)
                VALUES (" . $userId . " , " . $params["text"] . " , " . $params["style"] . ")";
        $DB->Query($sql);
    }

    static function setLabelToMessage($params) {
        global $DB;
        $sql = "
            INSERT INTO " . DB_TABLE_LABELS_PROPERTY . " (MESSAGE_ID, LABEL_ID)
                VALUES (" . $params["message_id"] . " , " . $params["label_id"] . ")";
        $DB->Query($sql);
    }

    static function getMessageLabels($messId) {
        global $DB;
        $sql = "SELECT 
                    LABEL_ID as ids FROM " . DB_TABLE_LABELS_PROPERTY . " WHERE MESSAGE_ID=" . $messId;
        $rows = $DB->Query($sql);
        $labelsID = array();
        $userLabelsId = Messages::getUserlabels(Utils::currUserId());
        while ($row = $rows->Fetch()) {
            if (in_array($row["ids"], $userLabelsId)) {
                $labelsID[] = $row["ids"];
            }
        }
        return $labelsID;
    }

    static function getUserlabels($userId) {
        global $DB;
        $sql = "SELECT ID FROM " . DB_TABLE_LABELS . " WHERE USER_ID=" . $userId;
        $rows = $DB->Query($sql);
        $labelsID = array();
        while ($row = $rows->Fetch()) {
            $lablsID[] = $row["id"];
        }
        return $labelsID;
    }

    static function getMessageStatus($messId) {
        global $DB;
        $sql = "SELECT
                    MES_STATUS
                FROM " . DB_TABLE_MESSAGES . " 
                WHERE ID=" . $messId;
        $rows = $DB->Query($sql);
        while ($row = $rows->Fetch()) {
            $status = $row["MES_STATUS"];
            return $status;
        }
    }

    static function getMessageInfo($messId) {
        global $DB;
        $sql = "SELECT * FROM " . DB_TABLE_MESSAGES . " WHERE ID=" . $messId;
        $rows = $DB->Query($sql);
        $mes = [];
        while ($row = $rows->Fetch()) {
            $mes["theme"] = $row["THEME"];
            $mes["comment"] = $row["COMMENT"];
            $mes["status"] = $row["MES_STATUS"];
            $mes["time"] = $row["TIMESTAMP_X"];
        }
        $mes["labels"] = Messages::getMessageLabels($messId);
        $mes["sender"] = Messages::getSenderId($messId);
        $mes["recepient"] = Messages::getRecepientId($messId);
        if ($mes['status'] == "REJECTED") {
            $mes['rejected_comment'] = $rows['REJECTED_COMMENT'];
        }
        $mes["docs"] = Messages::getDocsInMessage($messId);
        return $mes;
    }

    /**
     * @param array $params[]
     * 
     * 
     * 
     */
    
    static function createDraft($params) {
        global $DB;

        $sql = 'INSERT INTO ' . DB_TABLE_MESSAGES . ' ( SENDER_ID';
        if ($params['recepientId'])
            $sql .= ', RECEPIENT_ID';
        if ($params['theme'])
            $sql .= ', THEME';
        if ($params['comment'])
            $sql .= ', COMMENT';
        $sql .= ', TIMESTAMP_X, MES_STATUS) ';
        $sql .= 'VALUES( "' . Utils::currUserId() . '"';
        if ($params['recepientId'])
            $sql .= ', "' . $params['recepientId'] . '"';
        if ($params['theme'])
            $sql .= ", '" . $params['theme'] . "'";
        if ($params['comment'])
            $sql .= ", '"  . $params['comment'] . "'";
        $sql .= ", NOW(), 'DRAFT');";
        $DB->Query($sql);
        $mes = $DB->LastID();
        
        foreach($params["docsIds"] as $docId) {
            // $sql = 'INSERT INTO ' . DB_TABLE_MESSAGES_PROPERTY . " (MESSAGE_ID, DOC_ID) VALUES (" . $mes . ", " . $docId . ");";
            // $DB->Query($sql);
            Messages::setMesProp($mes, $docId);
        };
        return $mes;
    }

    static function setMesProp($mes, $docId) {
        global $DB;
        $sql = 'INSERT INTO ' . DB_TABLE_MESSAGES_PROPERTY . ' (MESSAGE_ID, DOC_ID) VALUES (' . $mes . ', ' . $docId . ');';
        $DB->Query($sql);
    }

    /**
     * @param array $params[]
     * 
     */

    static function updateDraft($params) {
        global $DB;
        $sql = 'UPDATE ' . DB_TABLE_MESSAGES . ' SET (TIMESTAMP_X=NOW()';
        if ($params['recepientId'])
            $sql .= ', RECEPIENT_ID=' . $params['recepientId'];
        if ($params['theme'])
            $sql .= ', THEME="' . $params['theme'] . '"';
        if ($params['comment'])
            $sql .= ', COMMENT' . $params['comment'] . '"';
        $sql .= ') ';
        $DB->Query($sql);
        $sql = '';
    }

    static function deleteDraft($params) {
        global $DB;
        $sql = 'DELETE FROM ' . DB_TABLE_MESSAGES . ' WHERE ID=' . $params['draftId'];
        $DB->Query($sql);
        $sql = 'DELETE FROM ' . DB_TABLE_MESSAGES_PROPERTY . ' WHERE MESSAGE_ID=' . $params['draftId'];
        $DB->Query($sql);
    }

    static function sendMessage($params) {
        global $DB;
        $docsId = Messages::getDocsInMessage($params['messId']);
        $recepientId = Messages::getRecepientId($params['messId']);
        foreach($docsId as $docId) {
            $sql = 'INSERT INTO ' . DB_TABLE_PROPERTY . ' (DOCUMENT_ID, TYPE, VALUE) ';
            $sql .= 'VALUES("'. $docId . '", "SHARE_READ", "' . $recepientId . '")'; 
            $DB->Query($sql);
            $sql = 'INSERT INTO ' . DB_TABLE_PROPERTY . ' (DOCUMENT_ID, TYPE, VALUE) ';
            $sql .= 'VALUES("'. $docId . '", "SHARE_SIGN", "' . $recepientId . '")'; 
            $DB->Query($sql);
        }

        $sql = 'UPDATE ' . DB_TABLE_MESSAGES . ' SET MES_STATUS = "NOT_READED" WHERE ID=' . $params['messId'] ;
        $DB->Query($sql);
    }

    static function isMessageExists($messId) {
        global $DB;
        $sql = 'SELECT count(*) FROM ' . DB_TABLE_MESSAGES . ' WHERE ID=' . $messId;
        $rows = $DB->Query($sql);
        if (($rows->SelectedRowsCount())==0) {
            return false;
        } else {
            return true;
        }
    }

    static function getDocsInMessage($messId) {
        global $DB;
        $sql = 'SELECT DOC_ID FROM ' . DB_TABLE_MESSAGES_PROPERTY . ' WHERE MESSAGE_ID=' . $messId;
        $rows = $DB->Query($sql);
        $docsId = [];
        while ($row = $rows->Fetch()) {
            $docsId[] = $row["DOC_ID"];
        }
        return $docsId;
    }

    static function isDocumentInMessage($docId) {
        global $DB;
        $sql = 'SELECT count(*) as count FROM ' . DB_TABLE_MESSAGES_PROPERTY . ' WHERE DOC_ID="' . $docId . '"';
        $rows = $DB->Query($sql);
        while ($row = $rows->Fetch()) {
            $count = $row['count'];
        };
        return $count == 0 ? false : true;
    }

    /**
     * @param array $params:[messId] - message id
     *                      [newStatus] - new status of message may be: DRAFT
     *                                                                  NOT_READED
     *                                                                  READED
     *                                                                  REJECTED
     *                                                                  RECALLED
     *                      [comment] - comment when message is reject
     *                      []
     */

    static function changeStatus($params) {
        global $DB;
        $sql = 'UPDATE ' . DB_TABLE_MESSAGES . ' SET MES_SATAUS = "' . $params["newStatus"] . '" WHERE ID = ' . $params['mess'];
        $DB->Query($sql);
        if($params['comment']) {
            $sql = 'UPDATE ' . DB_TABLE_MESSAGES . ' SET REJECTED_COMMENT = "' . $params['comment'] . '"';
            $DB->Query($sql);
        };
    }
}

/**
 * property: SHARE_READ - простая отправка  
 *           SHARE_SIGN - требуется подпись  
 *           
 * status: DRAFT - черновик
 *         NOT_READED - непрочитано
 *         READED - прочитано
 *         REJECTED - отклонено
 *         RECALLED - отозвано
 * 
 */