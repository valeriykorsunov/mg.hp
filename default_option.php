<?
$k30_bogdo_default_option = array();

if( CModule::IncludeModule("k30.bogdo") )
{
	$k30_bogdo_default_option = CK30BogdoSetings::GetFields();
}
?>