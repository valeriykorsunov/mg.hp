<?
$mg_hp_default_option = array();

if( CModule::IncludeModule("mg.hp") )
{
	$mg_hp_default_option = MgHpSettings::GetFields();
}
?>