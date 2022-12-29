<?
if ( file_exists( $_SERVER["DOCUMENT_ROOT"]."/local/modules/k30.bogdo/admin/customProjectSettings.php" ) )
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/k30.bogdo/admin/customProjectSettings.php");
}
else
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/k30.bogdo/admin/customProjectSettings.php");	
}
?>