<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?
?>
<tr>
	<td align="right"><span class="adm-required-field"><?= GetMessage("BPAR_PD_USER") ?>:</span></td>
	<td><?=CBPDocument::ShowParameterField("user", 'Responsible', $arCurrentValues['Responsible'], Array('size'=>'30'))?></td>
</tr>
<tr>
	<td align="right"><span class="adm-required-field"><?= GetMessage("BPAR_PD_NAME") ?>:</span></td>
	<td><?=CBPDocument::ShowParameterField("string", 'Name', $arCurrentValues['Name'], Array('size'=>'30'))?>
</td>
</tr>
<tr>
	<td align="right"><span class="adm-required-field"><?= GetMessage("BPAR_PD_DOC_ID") ?>:</span></td>
	<td><?=CBPDocument::ShowParameterField("string", 'docId', $arCurrentValues['docId'], Array('size'=>'30'))?>
</td>
</tr>
