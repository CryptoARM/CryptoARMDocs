<?php

defined('B_PROLOG_INCLUDED') || die;

use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('trusted.cryptoarmdocs');

$APPLICATION->SetTitle(Loc::getMessage('TR_CA_DOCS_CRM_LIST_TITLE'));

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/trusted.cryptoarmdocs/admin/trusted_cryptoarm_docs.php');

$schema = array(
    'GRID_ID' => 'crm_docs_grid',
    'SHOW_PAGINATION' => false,
    'SHOW_PAGESIZE' => false,
    'SHOW_TOTAL_COUNTER' => false,
    'ALLOW_SORT' => false,
    'STRUCTURE' => array(
        'ID' => array(
            'NAME' => 'ID',
            'TYPE' => 'int',
            'DEFAULT' => true,
            'SORT' => true,
            'FILTER_TYPE' => 'text',
        ),
        'NAME' => array(
            'NAME' => Loc::getMessage('TR_CA_DOCS_COL_FILENAME'),
            'TYPE' => 'text',
            'DEFAULT' => true,
            'SORT' => true,
            'FILTER_TYPE' => 'text',
        ),
        'SIGNATURES' => array(
            'NAME' => Loc::getMessage('TR_CA_DOCS_COL_SIGN'),
            'TYPE' => 'text',
            'DEFAULT' => true,
            'SORT' => true,
            'FILTER_TYPE' => 'text',
        ),
        'STATUS' => array(
            'NAME' => Loc::getMessage('TR_CA_DOCS_COL_STATUS'),
            'TYPE' => 'text',
            'DEFAULT' => true,
            'SORT' => true,
        ),
    ),
);

$gridBuilder = new Docs\GridBuilder($schema);

// print_r($gridBuilder->filter);
// print_r($gridBuilder->sort);
// print_r($gridBuilder->pagination);
$docs = Docs\Database::getDocumentsByUser($USER->GetID(), true);

$rows = array();

foreach ($docs->getList() as $doc) {
    $docId = $doc->getId();

    $signatures = $doc->getSignaturesToArray();

    $docStatus = $doc->getStatus();

    $docStatusString = Docs\Utils::GetTypeString($doc);
    if ($docStatus !== DOC_STATUS_NONE) {
        $docStatusString .= '<br>' .
            Loc::getMessage('TR_CA_DOCS_STATUS') .
            Docs\Utils::GetStatusString($doc);
    }

    $actions = array();
    if ($docStatus === DOC_STATUS_NONE) {
        $actions[] = array(
            'text' => Loc::getMessage('TR_CA_DOCS_ACT_SIGN'),
            'onclick' => "trustedCA.sign([$docId], null, {$gridBuilder->reloadGridJs})",
            'default' => true,
        );
    }
    if ($docStatus === DOC_STATUS_BLOCKED) {
        $actions[] = array(
            'text' => Loc::getMessage('TR_CA_DOCS_ACT_UNBLOCK'),
            'onclick' => "trustedCA.unblock([$docId], {$gridBuilder->reloadGridJs})",
            'default' => true,
        );
    }
    $actions[] = array(
        'text' => Loc::getMessage('TR_CA_DOCS_CRM_ACT_DOC_PAGE'),
        'onclick' => "window.location.href='$docId/'",
        'default' => false,
    );
    $actions[] = array(
        'text' => Loc::getMessage('TR_CA_DOCS_ACT_REMOVE'),
        'onclick' => "trustedCA.remove([$docId], false, {$gridBuilder->reloadGridJs})",
        'default' => false,
    );

    $downloadJs = "trustedCA.download([$docId], true)";
    $docName = "<a style='cursor:pointer;' onclick='$downloadJs' title='" . Loc::getMessage('TR_CA_DOCS_DOWNLOAD_DOC') . "'>{$doc->getName()}</a>";
    $rows[] = array(
        'id' => $docId,
        'columns' => array(
            'ID' => $docId,
            'NAME' => $docName,
            'SIGNATURES' => $doc->getSignaturesToTable(),
            'STATUS' => $docStatusString,
        ),
        'actions' => $actions,
    );
}

$gridBuilder->showGrid($rows);
?>

<script>
let trustedCAUploadHandler = (data) => {
    <?= $gridBuilder->reloadGridJs ?>()
};

let trustedCACancelHandler = (data) => {
    <?= $gridBuilder->reloadGridJs ?>()
};
</script>

