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

   /**
    * Returns Ids of all outgoing messages
    * @param array $params ["userId"]: id of user
    *                      ["typeOfMessage"]: if 'DRAFT' retuns all the drafts
    *                      ["firstElem"]: first needed message number for pagination
    *                      ["count"]: count of messages on page
    * @return array $messages: Ids of messages
    */
    static function getOutgoingMessages($params) {
        global $DB;
        $sql = "SELECT ID FROM " . DB_TABLE_MESSAGES . " WHERE SENDER_ID=" . $params["userId"] . ' AND ';
        if($params['typeOfMessage'] == 'drafts')
        {
            $sql .= 'MES_STATUS = "DRAFT" ';
        } else {
            $sql .= 'MES_STATUS <> "DRAFT"';
        }
        if ($params["firstElem"] && $params["count"]) {
            $sql .= ' LIMIT ' . $params["firstElem"] . ', ' . $params["count"];
        }
        $rows = $DB->Query($sql);
        $messages = [];
        while ($row = $rows->Fetch()) {
            $messages[] = $row["ID"];
        };
        return $messages;
    }

   /**
    * Returns Ids of all the incoming messages
    * @param array $params [userId]: id of user
    *                      [firstElem]: first needed message number for pagination
    *                      [count]: count of messages on page
    * @return array $messages: Ids of messages
    */
    static function getIncomingMessages($params) {
        global $DB;
        $sql = "SELECT ID FROM " . DB_TABLE_MESSAGES . " WHERE RECEPIENT_ID=" . $params['userId'] . ' AND  MES_STATUS <> "DRAFT"';
        if ($params["firstElem"] && $params["count"]) {
            $sql .= ' LIMIT ' . $params["firstElem"] . ', ' . $params["count"];
        }
        $rows = $DB->Query($sql);
        $messages = [];
        while ($row = $rows->Fetch()) {
            $messages[] = $row["ID"];
        };
        return $messages;
    }

    /**
     * @param array $params [searchKey]
     *                      [typeOfMessage]
     *                      [userId]
     * @return array $messageIDS
     */
    static function searchMessage($params) {
        global $DB;
        $sql = 'SELECT DISTINCT TDM.ID as ID FROM ' . DB_TABLE_MESSAGES_PROPERTY . ' as TDMP  RIGHT JOIN ' . DB_TABLE_DOCUMENTS . ' as TDD ';
        $sql .= 'ON (TDMP.DOC_ID = TDD.ID) ';
        $sql .= 'RIGHT JOIN ' . DB_TABLE_MESSAGES . ' as TDM ON ';
        $sql .= '(TDMP.MESSAGE_ID = TDM.ID) ';
        $messageIDS = [];
        if ($params['typeOfMessage'] != 'all') {
            $sql .= 'RIGHT JOIN b_user as BU ON (';
            if ($params["typeOfMessage"] == 'outgoing' || $params["typeOfMessage"] == 'draft') {
                $sql .= 'TDM.RECEPIENT_ID = BU.ID )';
            } else if ($params['typeOfMessage'] == 'incoming') {
                $sql .= 'TDM.SENDER_ID = BU.ID )';
            }
            $sql .= 'WHERE ((';
            $sql .= 'LOWER(BU.EMAIL) LIKE LOWER("%' . $params['searchKey'] . '%") OR ';
            $sql .= 'LOWER(TDD.NAME) LIKE LOWER("%' . $params['searchKey'] . '%") OR ';
            $sql .= 'LOWER(TDM.COMMENT) LIKE LOWER("%' . $params['searchKey'] . '%") OR ';
            $sql .= 'LOWER(TDM.THEME) LIKE LOWER("%' . $params['searchKey'] . '%")) AND ( ';
            if ($params["typeOfMessage"] == 'outgoing' || $params["typeOfMessage"] == 'draft') {
                $sql .= 'TDM.SENDER_ID = ' . $params['userId'];
                if ($params['typeOfMessage'] == 'draft') {
                    $sql .= ' AND TDM.MES_STATUS = "DRAFT"))';
                } else {
                    $sql .= ' AND TDM.MES_STATUS <> "DRAFT"))';
                }
            } else if ($params['typeOfMessage'] == 'incoming') {
                $sql .= 'TDM.RECEPIENT_ID = ' . $params['userId'] . ' AND TDM.MES_STATUS <> "DRAFT"))';
            };
            $rows = $DB->Query($sql);
            while ($row = $rows->Fetch()) {
                $messageIDS[] = $row['ID'];
            }
        } else {
            $sql .= 'RIGHT JOIN b_user AS BUS ON (TDM.SENDER_ID = BUS.ID) ';
            $sql .= 'RIGHT JOIN b_user AS BUR ON (TDM.RECEPIENT_ID = BUR.ID) ';
            $sql .= 'WHERE (( TDM.SENDER_ID=' . $params['userId'] . " AND ";
            $sql .= '(LOWER(TDM.COMMENT) LIKE LOWER("%' . $params['searchKey'] . '%") OR ';
            $sql .= 'LOWER(TDD.NAME) LIKE LOWER("%' . $params['searchKey'] . '%") OR ';
            $sql .= 'LOWER(TDM.THEME) LIKE LOWER("%' . $params['searchKey'] . '%") OR ';
            $sql .= 'LOWER(BUR.EMAIL) LIKE LOWER("%' . $params['searchKey'] . '%") )) OR ((';
            $sql .= 'TDM.MES_STATUS <> "DRAFT" AND TDM.RECEPIENT_ID = ' . $params['userId'] . ') AND (';
            $sql .= 'LOWER(TDM.COMMENT) LIKE LOWER("%' . $params['searchKey'] . '%") OR ';
            $sql .= 'LOWER(TDD.NAME) LIKE LOWER("%' . $params['searchKey'] . '%") OR ';
            $sql .= 'LOWER(TDM.THEME) LIKE LOWER("%' . $params['searchKey'] . '%") OR ';
            $sql .= 'LOWER(BUS.EMAIL) LIKE LOWER("%' . $params['searchKey'] . '%") )))';
            $rows = $DB->Query($sql);
            while ($row = $rows->Fetch()) {
                $messageIDS[] = $row['ID'];
            }
        }
        return  $messageIDS;
    }

    /**
     * @param array $params [searchKey]
     *                      [userId]
     *
     */
    static function searchLabel($params) {
        global $DB;
        $sql = 'SELECT * FROM ' . DB_TABLE_LABELS . ' WHERE (';
        $sql .= ' USER_ID = ' . $params["userId"] . ' AND ';
        $sql .= ' LOWER(TEXT) LIKE LOWER("%' . $params["searchKey"] . '%"))';
        $rows = $DB->Query($sql);
        $labels = [];
        while ($row = $rows->Fetch()) {
            $labels[] = [
                "id" => $row["ID"],
                "text" => $row["TEXT"],
                "style" => $row["STYLE"],
            ];
        }
        return $labels;
    }

    /**
     * Returns all searched messages ids
     * @param array $params [searchKey]: what need to find
     *                      [typeOfMessage]: what type of message need to find
     *                      [userId]: id of user
     * @return array $messageIDS: array of finded message ids;
     */

//    static function searchMessage($params) {
//        global $DB;
//        $messageIDS = [];
//        if ($params["typeOfMessage"] != 'all') {
//            $sql = 'SELECT TDM.ID as ID FROM ' . DB_TABLE_MESSAGES . ' as TDM RIGHT JOIN b_user as BU ON (';
//            if ($params["typeOfMessage"] == 'outgoing' || $params["typeOfMessage"] == 'draft') {
//                $sql .= 'TDM.RECEPIENT_ID = BU.ID';
//            } else if ($params["typeOfMessage"] == 'incoming') {
//                $sql .= 'TDM.SENDER_ID = BU.ID';
//            };
//            $sql .= ') ';
//            $sql .= 'WHERE ((';
//            $sql .= "BU.EMAIL LIKE '%" . $params["searchKey"] . "%' OR ";
//            $sql .= "TDM.COMMENT LIKE '%" . $params["searchKey"] . "%' OR ";
//            $sql .= "TDM.THEME LIKE '%" . $params["searchKey"] . "%') AND (";
//            if ($params["typeOfMessage"] == 'outgoing' || $params["typeOfMessage"] == 'draft') {
//                $sql .= 'TDM.SENDER_ID = ' . $params['userId'];
//                if ($params['typeOfMessage'] == 'draft') {
//                    $sql .= ' AND TDM.MES_STATUS = "DRAFT"))';
//                } else {
//                    $sql .= ' AND TDM.MES_STATUS <> "DRAFT"))';
//                }
//            } else if ($params['typeOfMessage'] == 'incoming') {
//                $sql .= 'TDM.RECEPIENT_ID = ' . $params['userId'] . ' AND TDM.MES_STATUS <> "DRAFT"))';
//            }
//            $rows = $DB->Query($sql);
//            while ($row = $rows->Fetch()) {
//                $messageIDS[] = $row['ID'];
//            }
//        } else {
//            $sql = 'SELECT TDM.ID AS ID FROM ' . DB_TABLE_MESSAGES . ' AS TDM ';
//            $sql .= 'RIGHT JOIN b_user AS BUS ON (TDM.SENDER_ID = BUS.ID) ';
//            $sql .= 'RIGHT JOIN b_user AS BUR ON (TDM.RECEPIENT_ID = BUR.ID)';
//            $sql .= 'WHERE (( TDM.SENDER_ID=' . $params['userId'] . " AND ";
//            $sql .= '( TDM.COMMENT LIKE "%' . $params['searchKey'] . '%" OR ';
//            $sql .= 'TDM.THEME LIKE "%' . $params['searchKey'] . '%" OR ';
//            $sql .= 'BUR.EMAIL LIKE "%' . $params['searchKey'] . '%" )) OR ((';
//            $sql .= 'TDM.MES_STATUS <> "DRAFT" AND TDM.RECEPIENT_ID = ' . $params['userId'] . ') AND (';
//            $sql .= 'TDM.COMMENT LIKE "%' . $params['searchKey'] . '%" OR ';
//            $sql .= 'TDM.THEME LIKE "%' . $params['searchKey'] . '%" OR ';
//            $sql .= 'BUS.EMAIL LIKE "%' . $params['searchKey'] . '%" )))';
//            $rows = $DB->Query($sql);
//            while ($row = $rows->Fetch()) {
//                $messageIDS[] = $row['ID'];
//            }
//        }
//        return $messageIDS;
//    }

   /**
    * Returns sender id
    * @param int $messId: id of message
    *
    * @return int $senderId: id of sender
    */
    static function getSenderId($messId) {
        global $DB;
        $sql = "SELECT SENDER_ID FROM " . DB_TABLE_MESSAGES . " WHERE ID=" . $messId;
        $rows = $DB->Query($sql);
        $row = $rows->Fetch();
        $senderId = $row["SENDER_ID"];
        return $senderId;
    }

    /**
    * Returns recepient id
    * @param int $messId: id of message
    *
    * @return int $recepientId: id of recepient
    */
    static function getRecepientId($messId) {
        global $DB;
        $sql = "SELECT RECEPIENT_ID FROM " . DB_TABLE_MESSAGES . " WHERE ID=" . $messId;
        $rows = $DB->Query($sql);
        $row = $rows->Fetch();
        $recepientId = $row["RECEPIENT_ID"];
        return $recepientId;
    }

    static function createLabel($params) {
        global $DB;
        $userId = Utils::currUserId();
        $sql = "
            INSERT INTO " . DB_TABLE_LABELS . " (USER_ID, TEXT, STYLE)
                VALUES (" . $userId . ' , "' . $params["text"] . '" , "'  . $params["style"] . '")';
        $DB->Query($sql);
    }

    static function setLabelToMessage($params) {
        global $DB;
        $sql = "
            INSERT INTO " . DB_TABLE_LABELS_PROPERTY . " (MESSAGE_ID, LABEL_ID)
                VALUES (" . $params["messageId"] . " , " . $params["labelId"] . ")";
        $DB->Query($sql);
    }

    static function getMessageLabels($messId) {
        global $DB;
        $sql = "SELECT
                    LABEL_ID as ids FROM " . DB_TABLE_LABELS_PROPERTY . " WHERE MESSAGE_ID=" . $messId;
        $rows = $DB->Query($sql);
        $labelsID = [];
        $userLabels = Messages::getUserlabels(Utils::currUserId());
        $userLabelsId = [];
        foreach ($userLabels as $userLabel) {
            $userLabelsId[] = $userLabel['id'];
        }
        while ($row = $rows->Fetch()) {
            if (in_array($row["ids"], $userLabelsId)) {
                $labelsID[] = $row["ids"];
            }
        }
        return $labelsID;
    }

    static function getLabelInfo($labelId) {
        global $DB;
        $sql = "SELECT * FROM " . DB_TABLE_LABELS . " WHERE ID = " . $labelId;
        $rows = $DB->Query($sql);
        $row = $rows->Fetch();
        $label['id'] = $labelId;
        $label['user'] = $row['USER_ID'];
        $label['text'] = $row['TEXT'];
        $label['style'] = $row['STYLE'];
        return $label;
    }

    static function isMessageWithThisLabel($messId, $labelId) {
        global $DB;
        $sql = "SELECT * FROM " . DB_TABLE_LABELS_PROPERTY . " WHERE (LABEL_ID=" . $labelId . " AND MESSAGE_ID=" . $messId . " )";
        $rows = $DB->Query($sql);
        if (($rows->SelectedRowsCount())==0) {
            return false;
        } else {
            return true;
        }
    }

//    static function getMessageLabels($messId, $userId) {
//        global $DB;
//        $sql = 'SELECT TDL.ID, TDL.TEXT, TDL.STYLE FROM ' . DB_TABLE_LABELS_PROPERTY . ' as TDLP RIGHT JOIN ' . DB_TABLE_LABELS . ' as TDL';
//        $sql .= ' ON (TDLP.LABEL_ID = TDL.ID) WHERE (TDLP.MESSAGE_ID = ' . $messId . ')';
//        $rows = $DB->Query($sql);
//        $labels = [];
//
//        while ($row = $rows->Fetch()) {
//            $labels[] = [
//                "id" => $row["TDL.ID"],
//                "text" => $row["TDL.TEXT"],
//                "style" => $row["TDL.STYLE"],
//            ];
//        }
//        return $labels;
//    }

    static function editLabel($params) {
        global $DB;
        $sql = "UPDATE " . DB_TABLE_LABELS . ' SET( ';
        if ($params['newText']) {
            $sql .= 'TEXT="' . $params["newText"] . '"';
            if ($params['newStyle']) {
                $sql .= ', ';
            }
        }
        if ($params['newStyle']) {
            $sql .= 'STYLE="' . $params['newStyle'] . '"';
        }
        $sql .= ') WHERE ID=' . $params["labelId"];
        $DB->Query($sql);
    }

    static function getUserlabels($userId) {
        global $DB;
        $sql = "SELECT * FROM " . DB_TABLE_LABELS . " WHERE USER_ID=" . $userId;
        $rows = $DB->Query($sql);
        $labels = [];
        while ($row = $rows->Fetch()) {
            $labels[] = [
                'id' => $row["ID"],
                'text' => $row["TEXT"],
                'style' => $row["STYLE"],
            ];
        }
        return $labels;
    }

   /**
    * Returns status of message
    * @param int $messId: id of message
    *
    * @return string $status: status of document
    */
    static function getMessageStatus($messId) {
        global $DB;
        $sql = "SELECT
                    MES_STATUS
                FROM " . DB_TABLE_MESSAGES . "
                WHERE ID=" . $messId;
        $rows = $DB->Query($sql);
        $row = $rows->Fetch();
        $status = $row["MES_STATUS"];
        return $status;
    }

//    static function getMessagesByLabel($labelId, $userId) {
//        global $DB;
//        $sql = 'SELECT MESSAGE_ID FROM ' . DB_TABLE_LABELS_PROPERTY . ' WHERE LABEL_ID=' . $labelId;
//        $rows = $DB->Query($sql);
//        $messageLabels = [];
//        $userLabels = Messages::getUserLabels($userId);
//        while ($row = $rows->Fetch()) {
//            if(in_array($row["MESSAGE_ID"], $userLabels)) {
//                $messageLabels[] = $row["MESSAGE_ID"];
//            }
//        }
//        return $messageLabels;
//    }

    static function getMessagesByLabel($labelId, $userId) {
        global $DB;
        $sql = 'SELECT TDLP.MESSAGE_ID as label FROM ' . DB_TABLE_LABELS_PROPERTY . ' as TDLP RIGHT JOIN ' . DB_TABLE_LABELS . ' as TDL ON(';
        $sql .= 'TDLP.LABEL_ID = TDL.ID) WHERE (TDLP.LABEL_ID=' . $labelId . ' AND TDL.USER_ID=' . $userId . ')';
        $rows = $DB->Query($sql);
        $messageLabels = [];
        while ($row = $rows->Fetch()) {
            $messageLabels[] = $row["label"];
        }
        return $messageLabels;
    }

   /**
    * @param int $messId: id of message
    *
    * @return array $mes [theme] theme of message
    *                    [comment] comment to message
    *                    [status] status of message
    *                    [time] time when message was created
    *                    [labels] ids of labels
    *                    [sender] id of sender
    *                    [recepiend] id of recepient
    *                    [rejectedComment] comment to message if rejected
    *                    [docs] ids of docs in message
    */
    static function getMessageInfo($messId) {
        global $DB;
        $sql = "SELECT * FROM " . DB_TABLE_MESSAGES . " WHERE ID=" . $messId;
        $rows = $DB->Query($sql);
        $mes = [];
        $row = $rows->Fetch();
        $mes["theme"] = $row["THEME"];
        $mes["comment"] = $row["COMMENT"];
        $mes["status"] = $row["MES_STATUS"];
        $mes["time"] = $row["TIMESTAMP_X"];
        $mes['rejectedComment'] = $row['REJECTED_COMMENT'];
        $labels = Messages::getMessageLabels($messId);
//        $mes["labels"] = Messages::getMessageLabels($messId);
        foreach ($labels as $label) {
            $mes["labels"][] = Messages::getLabelInfo($label);
        }
        $mes["sender"] = Messages::getSenderId($messId);
        $mes["recepient"] = Messages::getRecepientId($messId);
        $mes["docs"] = Messages::getDocsInMessage($messId);
        return $mes;
    }

   /**
    * Returns all the new messages
    * @param int $userId: id of user
    *
    * @return int $mess: ids of new messages
    */
    static function getNewIncomingMessages($userId) {
        global $DB;
        $sql = 'SELECT ID FROM ' . DB_TABLE_MESSAGES . " WHERE RECEPIENT_ID = " . $userId . ' AND MES_STATUS="NOT_READED"';
        $rows = $DB->Query($sql);
        $mess = [];
        while ($row = $rows->Fetch()) {
            $mess[] = $row["count"];
        }
        return $mess;
    }

    /**
     * Create new draft
     * @param array $params[recepientId]: id of recepient
     *                     [theme]: theme of message
     *                     [comment]: comment to message
     *
     * @return int $mes: id of new draft
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
            Messages::setMesProp($mes, $docId);
        };
        return $mes;
    }

   /**
    * Assigns document to message
    * @param int $mes: id of message
    * @param int $docId: id of document
    *
    */
    static function setMesProp($mes, $docId) {
        global $DB;
        $sql = 'INSERT INTO ' . DB_TABLE_MESSAGES_PROPERTY . ' (MESSAGE_ID, DOC_ID) VALUES (' . $mes . ', ' . $docId . ');';
        $DB->Query($sql);
    }

    /**
     * Change draft
     * @param array $params[recepientId]: id of recepient
     *                     [theme]: theme of message
     *                     [comment]: comment to message
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

   /**
    * remove draft from database
    * @param array $params [draftId]: id of draft
    *
    */
    static function deleteDraft($params) {
        global $DB;
        $sql = 'DELETE FROM ' . DB_TABLE_MESSAGES . ' WHERE ID=' . $params['draftId'];
        $DB->Query($sql);
        $sql = 'DELETE FROM ' . DB_TABLE_MESSAGES_PROPERTY . ' WHERE MESSAGE_ID=' . $params['draftId'];
        $DB->Query($sql);
    }

   /**
    * Change status of message from 'DRAFT' to 'NOT_READED' and open documents in message for recepient
    * @param array $params[messId]: id of message
    */
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

    /**
     * Change status of message to the draft and remove shares docs property
     * @param int $messId: id of message
     * @return boolean if false - it's too late for cancel
     */

    static function sendCancelInDB($params) {
        // global $DB;
        // $sql = 'SELECT TIMESTAMP_X as TIME FROM ' . DB_TABLE_MESSAGES . ' WHERE ID=' . $params['messId'];
        // $rows = $DB->Query($sql);
        // $row = $rows->Fetch();
        // $time = strtotime($row['TIME']);
        // $currTime = strtotime(date("Y-m-d H:i:s"));
        // $interval = $currTime - $time;
        // if ($interval > 1000) {
        //     return false;
        // } else {
        //     $sql = 'UPDATE ' . DB_TABLE_MESSAGES . ' SET MES_STATUS = "DRAFT" WHERE ID = ' . $params['messId'];
        //     $DB->Query($sql);
        //     $docsIds = Messages::getDocsInMessage($params['messId']);
        //     foreach ($docsIds as $docId) {
        //         $sql = 'DELETE FROM '  . DB_TABLE_PROPERTY . ' WHERE (DOCUMENT_ID = ' . $docId .' AND (TYPE = "SHARE_READ" OR TYPE = "SHARE_SIGN"))';
        //         $DB->Query($sql);
        //     }
        //     return true;
        // }
        global $DB;
        $status = Messages::getMessageStatus($params['messId']);
        if ($status != 'NOT_READED') {
            return false;
        } else {
            $sql = 'UPDATE ' . DB_TABLE_MESSAGES . ' SET MES_STATUS = "DRAFT" WHERE ID = ' . $params['messId'];
            $DB->Query($sql);
            $docsIds = Messages::getDocsInMessage($params['messId']);
            foreach ($docsIds as $docId) {
                $sql = 'DELETE FROM '  . DB_TABLE_PROPERTY . ' WHERE (DOCUMENT_ID = ' . $docId .' AND (TYPE = "SHARE_READ" OR TYPE = "SHARE_SIGN"))';
                $DB->Query($sql);
            }
            return true;
        }
    }

   /**
    * Check message existing in database
    * @param int $messId: id of message
    *
    * @return bool
    */
    static function isMessageExists($messId) {
        global $DB;
        $sql = 'SELECT * FROM ' . DB_TABLE_MESSAGES . ' WHERE ID=' . $messId;
        $rows = $DB->Query($sql);
        if (($rows->SelectedRowsCount())==0) {
            return false;
        } else {
            return true;
        }
    }

    static function isLabelExists($labelId) {
        global $DB;
        $sql = 'SELECT * FROM ' . DB_TABLE_LABELS . ' WHERE ID=' . $labelId;
        $rows = $DB->Query($sql);
        if (($rows->SelectedRowsCount())==0) {
            return false;
        } else {
            return true;
        }
    }

    /**
    * Returns all the documents in the message
    * @param int $messId: id of message
    *
    * @return array $docsId: ids of all the documents in message
    */
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

   /**
    * Check is document in message
    * @param int $docId: id of document
    *
    * @return bool
    */
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
    * returns all the message with this document
    * @param int $docId: id of document
    *
    * @return $messageIDS: id of all the message with this document
    */
    static function getMessagesByDocument($docId) {
        global $DB;
        $sql = 'SELECT MESSAGE_ID FROM ' . DB_TABLE_MESSAGES_PROPERTY . ' WHERE DOC_ID="' . $docId . '"';
        $rows =$DB->Query($sql);
        $messageIDS = [];
        while ($row = $rows->Fetch()) {
            $messageIDS[] = $row['MESSAGE_ID'];
        };
        return $messageIDS;
    }

    /**
     * @param array $params:[messId] - message id
     *                      [newStatus] - new status of message may be: DRAFT
     *                                                                  NOT_READED
     *                                                                  READED
     *                                                                  REJECTED
     *                                                                  RECALLED
     *                      [comment] - comment when message is reject
     */

    static function changeStatus($params) {
        global $DB;
        $sql = 'UPDATE ' . DB_TABLE_MESSAGES . ' SET MES_STATUS = "' . $params["newStatus"] . '" WHERE ID = ' . $params['mess'];
        $DB->Query($sql);
        if($params['comment']) {
            $sql = 'UPDATE ' . DB_TABLE_MESSAGES . ' SET REJECTED_COMMENT = "' . $params['comment'] . '"';
            $DB->Query($sql);
        };
    }

    static function removeLabel($labelId) {
        global $DB;
        $sql = 'DELETE FROM ' . DB_TABLE_LABELS . ' WHERE ID = ' . $labelId;
        $DB->Query($sql);
        $sql = 'DELETE FROM ' . DB_TABLE_LABELS_PROPERTY . ' WHERE LABEL_ID = ' . $labelId;
        $DB->Query($sql);
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