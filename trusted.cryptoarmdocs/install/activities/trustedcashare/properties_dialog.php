<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<tr>
  <td align="right" width="40%"><span class="adm-required-field"><?= Loc::getMessage('RESPONSIBLE') ?>:</span></td>
  <td width="60%">
    <?= CBPDocument::ShowParameterField('user','Responsible',$arCurrentValues['Responsible'],Array('rows'=> 1))?>
  </td>
</tr>

<tr>
  <td align="right" width="40%" valign="top"><span class="adm-required-field"><?= Loc::getMessage('DOCID') ?>:</span></td>
  <td width="60%" valign="top">
    <?= CBPDocument::ShowParameterField('int','rDocID',$arCurrentValues['rDocID'])?>
    <?= GetMessage("DOCID_EMPTY") ?>
  </td>
</tr>

<tr>
  <td align="right" width="40%" valign="top"><span class="adm-required-field">
  <td width="60%" valign="top">
    <?echo BeginNote();?>
      <?=GetMessage("DESCRIPTION")?><br>
      <?=GetMessage("DESCRIPTION2")?><br>
      <?=GetMessage("DESCRIPTION3")?>
    <?echo EndNote();?>
  </td>
</tr>