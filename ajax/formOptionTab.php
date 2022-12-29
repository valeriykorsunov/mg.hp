<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$request = Bitrix\Main\Context::getCurrent()->getRequest();

$paramsTab = array();
$paramsTab["ID_TAB"] = $request["ID_TAB"];

if($request["save"] != "" )
{
	if($paramsTab["ID_TAB"] == "new")
	{
		if($newtab = \K30\Bogdo\ModuleOptions::AddTab([
			"NAME" => $request["tabname"],
			"SORT" => $request["sortTab"]
		]))
		{
			$paramsTab["ID_TAB"] = $newtab;
		};
	}
	else
	{
		\K30\Bogdo\ModuleOptions::updateTab($paramsTab["ID_TAB"], [
			"NAME" => $request["tabname"],
			"SORT" => $request["sortTab"]
		]);

	}

	if(is_array($request["select"]))
	{
		\K30\Bogdo\ModuleOptions::UpdateUserFieldListforTab($paramsTab["ID_TAB"], $request["select"]);
	}
}
if($paramsTab["ID_TAB"] == "new")
{
	$paramsTab["ID_TAB"] = $paramsTab["ID_TAB"];
	$paramsTab["TAB_NAME"] = $request["tabname"];
	$paramsTab["TAB_SORT"] = "100";
}
else
{
// Получить новые данные для формы
	$tabInfo = \K30\Bogdo\ModuleOptions::GetTabsInfo($paramsTab["ID_TAB"]);

	$paramsTab["ID_TAB"] = $tabInfo["ID"];
	$paramsTab["TAB_NAME"] = $tabInfo["NAME"];
	$paramsTab["TAB_SORT"] = $tabInfo["SORT"];
}

$userfieldList = \K30\Bogdo\ModuleOptions::GetUserFieldList($paramsTab["ID_TAB"]);
?>

<form method="POST" id="optionsTab">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="save" value="<?= ($request["save"] != "" ? "Y" : "N")?>">
	<input type="hidden" name="ID_TAB" value="<?= $paramsTab["ID_TAB"]?>">

	<div class="tab-param">
		<div>Название вкладки: <input type="text" name="tabname" size="50" value="<?= $paramsTab["TAB_NAME"]?>"> </div>
		<div>Сортировка: <input type="text" name="sortTab" size="5" value="<?= $paramsTab["TAB_SORT"]?>"> </div>
	</div>
	<br>

	<table class="gcustomsettings-settings-tab-headers" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<td>ID</td>
				<td>Название настройки</td>
				<td>Код настройки</td>
				<td></td>
			</tr>
		</thead>
		<tbody class="js-tabs">
			<?foreach($userfieldList as $field):?>
			<tr>
				<td>
					<?= $field["ID"] ?>
				</td>
				<td>
				<?= $field["USER_FIELD_NAME"] ?>
				</td>
				<td>
				<?= $field["FIELD_NAME"] ?>
				</td>
				<td>
					<input type="checkbox" name="select[<?= $field["ID"] ?>]" <?if($field["SETTINGS_ID"]):?> checked="checked"<?endif?> value="<?= $field["SETTINGS_ID"]?>">
				</td>
			</tr>
			<?endforeach?>
		</tbody>
	</table>

	<? \K30\Bogdo\ModuleOptions::ShowJSformOptionTab() ?>
</form>