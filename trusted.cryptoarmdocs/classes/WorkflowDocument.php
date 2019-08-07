<?php
namespace Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\GroupTable;

if (!\CModule::IncludeModule('bizproc')) {
    return;
}

class WorkflowDocument implements \IBPWorkflowDocument {
    public static function getDocumentType($id) {
        return 'TR_CA_DOC';
    }

    public static function getComplexDocumentType() {
        return [TR_CA_DOCS_MODULE_ID, self::class, 'TR_CA_DOC'];
    }

    public static function getComplexDocumentId($id) {
        return [TR_CA_DOCS_MODULE_ID, self::class, $id];
    }

    public static function CreateDocument($pid, $fields) {
        $doc = Utils::CreateDocument($fields['file'], array_diff_key($fields, ['file' => true]));
        return $doc->getId();
    }

    public static function DeleteDocument($id) {
        Database::getDocumentById($id)
            ->getLastDocument()
            ->remove();
    }

    public static function GetDocument($id) {
        return Database::getDocumentById($id)
            ->getLastDocument()
            ->toArray();
    }

    public static function GetDocumentFields($documentType) {
        return [
            'id' => [
                'Name' => 'ID',
                'Type' => FieldType::INT,
                'Editable' => false,
                'Required' => true,
            ],
            'name' => [
                'Name' => Loc::getMessage('TR_CA_DOC_NAME'),
                'Type' => FieldType::STRING,
                'Editable' => true,
                'Required' => true,
            ],
            'path' => [
                'Name' => Loc::getMessage('TR_CA_DOC_PATH'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
            'type' => [
                'Name' => Loc::getMessage('TR_CA_DOC_TYPE'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
            'status' => [
                'Name' => Loc::getMessage('TR_CA_DOC_STATUS'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
            'signers' => [
                'Name' => Loc::getMessage('TR_CA_DOC_SIGNERS'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
            'signatures' => [
                'Name' => Loc::getMessage('TR_CA_DOC_SIGNATURES'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
            'hash' => [
                'Name' => Loc::getMessage('TR_CA_DOC_HASH'),
                'Type' => FieldType::STRING,
                'Editable' => false,
                'Required' => true,
            ],
        ];
    }

    public static function getDocumentAdminPage($id) {
        $doc = Database::getDocumentById($id);
        if (!$doc) {
            return '';
        }
        $lastDocId = $doc->getLastDocument()->getId();
        return "/bitrix/components/trusted/docs/ajax.php?command=content&id=$lastDocId";
    }

    public static function canUserOperateDocument(
        $operation,
        $userId,
        $documentId,
        $arParameters = array()
    ) {
        return true;
    }

    public static function canUserOperateDocumentType(
        $operation,
        $userId,
        $documentType,
        $arParameters = array()
    ) {
        // Can be used to disallow non-admins from accessing bp editor
        return true;
    }

    public static function UpdateDocument($id, $arFields, $modifiedById = null) {
    }

    public static function PublishDocument($id) {
        return false;
    }

    public static function UnpublishDocument($id) {
        return false;
    }

    public static function LockDocument($id, $workflowId) {
        return false;
    }

    public static function UnlockDocument($id, $workflowId) {
        $doc = Database::getDocumentById($id)->getLastDocument();
        $doc->unblock();
        $doc->save();
        return true;
    }

    public static function IsDocumentLocked($id, $workflowId) {
        return Database::getDocumentById($id)
            ->getLastDocument()
            ->getStatus() == DOC_STATUS_BLOCKED;
    }

    public static function getAllowableOperations($documentType) {
        return [];
    }

    public static function getAllowableUserGroups($documentType) {
        $dbAdminGroup = GroupTable::getById(1);
        $adminGroup = $dbAdminGroup->fetch();

        return array(
            'group_1' => $adminGroup['NAME'],
        );
    }

    public static function getUsersFromUserGroup($group, $id) {
        $group = strtolower($group);

        $groupId = intval(str_replace('group_', '', $group));
        if ($groupId <= 0) {
            return array();
        }

        return \CGroup::GetGroupUser($groupId);
    }

    public static function GetDocumentForHistory($id, $historyIndex) {
        return false;
    }

    public static function recoverDocumentFromHistory($id, $arDocument) {
        return false;
    }
}
