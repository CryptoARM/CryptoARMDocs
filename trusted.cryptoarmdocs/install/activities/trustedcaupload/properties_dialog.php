<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?
?>
<tr>
	<td align="right"><span class="adm-required-field"><?= GetMessage("BPAR_PD_USER") ?>:</span></td>
	<td><?=CBPDocument::ShowParameterField("user", 'Responsible', $arCurrentValues['Responsible'], Array('rows'=>1,'size'=>'30'))?></td>
</tr>
<tr>
	<td align="right"><span class="adm-required-field"><?= GetMessage("BPAR_PD_NAME") ?>:</span></td>
	<td><?=CBPDocument::ShowParameterField("string", 'Name', $arCurrentValues['Name'], Array('size'=>'30'))?>
</td>
</tr>
<tr>
	<td align="right"><?= GetMessage("BPAR_PD_RECIPIENT") ?>:</td>
	<td><?=CBPDocument::ShowParameterField("user", 'Recipient', $arCurrentValues['Recipient'], Array('rows'=>1,'size'=>'30'))?></td>
</tr>

<tr>
  <td align="center" width="40%" valign="top"><span class="adm-required-field">
  <td width="60%" valign="top">
    <?echo BeginNote();?>
      <?=GetMessage("DESCRIPTION")?><br>
	  <?=GetMessage("DESCRIPTION2")?><br>
    <?echo EndNote();?>
  </td>
</tr>