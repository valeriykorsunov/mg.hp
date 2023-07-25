<?

use Bitrix\Main\Localization\Loc;

if(!check_bitrix_sessid()) return false;

Loc::loadMessages(__FILE__);
?>

<form action="<?= $APPLICATION->GetCurPage();?>">
<?= bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?= LANGUAGE_ID?>">
<input type="hidden" name="id" value="mg.hp">
<input type="hidden" name="uninstall" value="Y">
<input type="hidden" name="step" value="2">
<?= CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN"))?>
<p><?= Loc::getMessage("MOD_UNINST_SAVE")?></p>
<p>
	<input type="checkbox" name="savedata" id="savedata" value="Y" checked>
	<label for="savedata">
		<?= Loc::getMessage("MOD_UNINST_SAVE_TABLES")?>
	</label>
	<input type="submit" name="" value="<?= Loc::getMessage("MOD_UNINST_DEL")?>">
</p>
</form>