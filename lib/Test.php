<?

namespace K30\Bogdo;

class Test
{
	static function dump()
	{
		echo"<pre>"; var_dump(substr(strrchr(dirname(__DIR__,3),"/"),1)); echo "</pre>";
	}
}
?>