<?
if ( file_exists( $_SERVER["DOCUMENT_ROOT"]."/local/modules/mg.hp/admin/customProjectSettings.php" ) )
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/mg.hp/admin/customProjectSettings.php");
}
else
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mg.hp/admin/customProjectSettings.php");	
}
?>