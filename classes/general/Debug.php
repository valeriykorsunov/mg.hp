<?

namespace Bogdo;


class Debug
{
	private static $arrayLog = array();

	/**
	 * Вывод данных в отладочную консоль javascript
	 * + backtrace 7 последних шагов
	 *
	 * @param $data
	 */
	static function console_log($data)
	{
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
		$json = json_encode(unserialize(str_replace(
			array('NAN;', 'INF;'),
			'0;',
			serialize($data)
		)));
		echo '<script>';
		echo 'console.group("' . $backtrace[0]["file"] . " - line: " . $backtrace[0]["line"] . '");';
		echo 'console.log(' . $json . ');';
		echo 'console.groupEnd();';
		echo '</script>';
	}

	/**
	 * Собирает логи в массив
	 * + backtrace 7 последних шагов
	 *
	 * @param [type] $data
	 */
	public static function consoleAdd($data)
	{
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7);

		$dataJson = [
			"BACKTRACE" => $backtrace,
			"DATA"=>$data
		];
	
		$json = json_encode(unserialize(str_replace(
			array('NAN;', 'INF;'),
			'0;',
			serialize($dataJson)
		)));

		self::$arrayLog[] = [
			"strGroup" => $backtrace[0]["file"] . " - line: " . $backtrace[0]["line"],
			"strLog" => $json
		];
	}

	/**
	 * выводит подготовленные логи в консоль javascript
	 * Используется в событии OnEpilog
	 *
	 * @return show script
	 */
	public static function showLog()
	{
		if(!empty(self::$arrayLog))
		{
			echo '<script>';
			foreach(self::$arrayLog as $arLog)
			{
				echo 'console.group("' . $arLog["strGroup"] . '");';
				echo 'console.log(' . $arLog["strLog"] . ');';
				echo 'console.groupEnd();';
			}
			echo '</script>';
		}		
	}
}

?>