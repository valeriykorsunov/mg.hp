<?
namespace K30\Bogdo;

class EventHandler {

	public static function onPageStart(){

	}

	public static function OnEpilog(){
		\Bogdo\Debug::showLog();
	}
}

?>