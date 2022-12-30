<?
namespace MG\HP\Main;

class EventHandler {

	public static function onPageStart(){

	}

	public static function OnEpilog(){
		\MG\HP\Debug::showLog();
	}
}

?>