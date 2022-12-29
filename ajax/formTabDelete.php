<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$request = Bitrix\Main\Context::getCurrent()->getRequest();

$paramsTab = array();
$paramsTab["ID_TAB"] = $request["ID_TAB"];

if($request["DELETE"] == "Y")
{
	\K30\Bogdo\ModuleOptions::deleteTab($paramsTab["ID_TAB"]);
	exit;
}
?>

<form method="POST" id="optionsTab" action="<?= \K30\Bogdo\ModuleOptions::GetPath(true) ?>/ajax/formTabDelete.php">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="DELETE" value="Y">
	<input type="hidden" name="ID_TAB" value="<?= $paramsTab["ID_TAB"]?>">
	<h2>Удалить вкладку?</h2>
</form>