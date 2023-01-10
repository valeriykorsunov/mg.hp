<?
namespace MG\HP\Main;

class EventHandler {

	public static function onPageStart(){

	}

	public static function OnEpilog(){
		\Bitrix\Main\Diag\Debug::dumpToFile("test 2.0" ,'*'.date('Y-m-d H:i:s').'*'. PHP_EOL .__FILE__);
		\MG\HP\Debug::showLog();
	}
}

?>