<?php

namespace Trusted\CryptoARM\Docs;

use Bitrix\Main\Loader;

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

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/general/IEntity.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/classes/general/ISave.php';

class RequireSign implements IEntity, ISave {

    protected $requireId = null;
    protected $docId = null;
    protected $userId = null;
    protected $emailStatus = null;
    protected $signStatus = null;

    function __construct() {
        $this->docId = null;
    }

    function __destruct() {

    }

    static function fromArray($array) {
        $doc = null;

        if ($array) {
            $doc = new RequireSign();
            $doc->setRequireId($array["ID"]);
            $doc->setDocId($array["DOCUMENT_ID"]);
            $doc->setUserId($array["USER_ID"]);
            $doc->setEmailStatus($array["EMAIL_STATUS"]);
            $doc->setSignStatus($array["SIGN_STATUS"]);
        }

        return $doc;
    }

    public function toArray()
    {
        $a = [
            "require_id" => $this->getRequireId(),
            "document_id" => $this->getDocId(),
            "user_id" => $this->getUserId(),
            "email_status" => $this->getEmailStatus(),
            "sign_status" => $this->getSignStatus(),
        ];

        return $a;
    }

    function getRequireId()
    {
        return (int)$this->requireId;
    }

    function setRequireId($requireId)
    {
        $this->requireId = (int)$requireId;
    }

    function getDocId()
    {
        return (int)$this->docId;
    }

    function setDocId($docId)
    {
        $this->docId = (int)$docId;
    }

    function getUserId()
    {
        return (int)$this->userId;
    }

    function setUserId($userId)
    {
        $this->userId = (int)$userId;
    }

    function getEmailStatus()
    {
        return $this->emailStatus;
    }

    function setEmailStatus($emailStatus)
    {
        $this->emailStatus = $emailStatus;
    }

    function getSignStatus()
    {
        return $this->signStatus;
    }

    function setSignStatus($signStatus)
    {
        $this->signStatus = $signStatus;
    }

    public function save()
    {

    }
}
