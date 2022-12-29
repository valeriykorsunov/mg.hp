<?

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
class k30_bogdo extends CModule
{

	function __construct()
	{
		$arModuleVersion = array();
		include(__DIR__ . '/version.php');

		$this->MODULE_ID = 'k30.bogdo';
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("K30_BOGDO_MODULE_NAME"); // имя модуля
		$this->MODULE_DESCRIPTION = Loc::getMessage("K30_BOGDO_MODULE_DESCRIPTION"); // описание модуля

		$this->PARTNER_NAME = Loc::getMessage("K30_BOGDO_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("K30_BOGDO_PARTNER_URI");

		$this->MODULE_SORT = 1;
		$this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
		$this->MODULE_GROUP_RIGHTS = 'Y';  //используем ли индивидуальную схему распределения прав доступа

		$this->NAME_DIRECTORY = substr(strrchr(dirname(__DIR__, 3), "/"), 1); // local или bitrix
	}

	function DoInstall()
	{
		global $APPLICATION;
		if ($this->isVersionD7() && $this->isVersionPhp())
		{
			ModuleManager::registerModule($this->MODULE_ID);

			RegisterModuleDependences("main", "OnUserTypeBuildList", "k30.bogdo", "СComplexUserProperty", "getDescription");

			Loader::includeModule($this->MODULE_ID);
			$this->InstallDB();
			$this->editHandler("install");
			$this->editFiles("install");
		}
		else
		{
			$APPLICATION->ThrowException(Loc::getMessage("K30_BOGDO_ERROR_VERSION"));
		}

		$APPLICATION->IncludeAdminFile(Loc::getMessage("K30_BOGDO_INSTALL_TITLE"), $this->GetPath() . "/install/step.php");
	}

	function DoUninstall()
	{
		if (!check_bitrix_sessid()) return false;
		global $APPLICATION;
		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();

		if ($request["step"] < 2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage("K30_BOGDO_UNINSTALL_TITLE"), $this->GetPath() . "/install/unstep1.php");
		}
		elseif ($request["step"] == 2)
		{
			if ($request["savedata"] != "Y")
			{
				$this->UnInstallDB();
			}

			$this->editHandler("uninstall");
			$this->editFiles("uninstall");

			UnRegisterModuleDependences("main", "OnUserTypeBuildList", "k30.bogdo", "СComplexUserProperty", "getDescription");

			ModuleManager::unRegisterModule($this->MODULE_ID);
			$APPLICATION->IncludeAdminFile(Loc::getMessage("K30_BOGDO_UNINSTALL_TITLE"), $this->GetPath() . "/install/unstep2.php");
		}
	}

	function InstallDB()
    {
		Loader::includeModule($this->MODULE_ID);

        if(!Application::getConnection(\K30\Bogdo\TabsTable::getConnectionName())->isTableExists(Base::getInstance('\K30\Bogdo\TabsTable')->getDBTableName()))
        {
            Base::getInstance('\K30\Bogdo\TabsTable')->createDbTable();
        }
		
        if(!Application::getConnection(\K30\Bogdo\TabsUserFieldUsTable::getConnectionName())->isTableExists(Base::getInstance('\K30\Bogdo\TabsUserFieldUsTable')->getDBTableName()))
        {
            Base::getInstance('\K30\Bogdo\TabsUserFieldUsTable')->createDbTable();
        }
	}
	
	function UnInstallDB()
	{
        Loader::includeModule($this->MODULE_ID);

        Application::getConnection(\K30\Bogdo\TabsTable::getConnectionName())->queryExecute('drop table if exists '.Base::getInstance('\K30\Bogdo\TabsTable')->getDBTableName());

        Application::getConnection(\K30\Bogdo\TabsUserFieldUsTable::getConnectionName())->queryExecute('drop table if exists '.Base::getInstance('\K30\Bogdo\TabsUserFieldUsTable')->getDBTableName());
	}

	protected function isVersionD7()
	{
		return CheckVersion(\Bitrix\Main\ModuleManager::getVersion("main"), "14.00.00"); // на этой версии я начал разработку.
	}

	protected function isVersionPhp($version = '7.4')
	{
		return (phpversion() * 10 >= $version * 10);
	}

	protected function GetPath($notDocumentRoot = false)
	{
		if ($notDocumentRoot)
			return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
		else
			return dirname(__DIR__);
	}

	/**
	 * Обработчик событий
	 *
	 * @param [string] $typeAction
	 * @return bool
	 */
	protected function editHandler(string $typeAction)
	{
		$listHendler = array(
			["ModuleId" => "main", "Event" => "onPageStart", "Sort" => "100"],
			["ModuleId" => "main", "Event" => "OnEpilog", "Sort" => "100"],
		);

		foreach ($listHendler as $params)
		{
			if ($typeAction == "install")
			{
				$this->registerHandler($params);
			}
			if ($typeAction == "uninstall")
			{
				$this->unregisterHandler($params);
			}
		}
		return	true;
	}
	protected function registerHandler(array $params)
	{
		if (!isset($params["ModuleId"], $params["Event"], $params["Sort"])) return false; // TODO зафиксировать как ошибку.

		\Bitrix\Main\EventManager::getInstance()->registerEventHandler(
			$params["ModuleId"],
			$params["Event"],
			$this->MODULE_ID,
			'K30\Bogdo\EventHandler',
			$params["Event"],
			$params["Sort"]
		);
		return true;
	}
	protected function unregisterHandler(array $params)
	{
		if (!isset($params["ModuleId"], $params["Event"], $params["Sort"])) return false; // TODO зафиксировать как ошибку.

		\Bitrix\Main\EventManager::getInstance()->unregisterEventHandler(
			$params["ModuleId"],
			$params["Event"],
			$this->MODULE_ID,
			'K30\Bogdo\EventHandler',
			$params["Event"]
		);
		return true;
	}

	/**
	 * Обработчик файлов
	 *
	 * @param string $typeAction
	 * @return void
	 */
	protected function editFiles(string $typeAction)
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/".$this->MY_DIR."/modules/".$this->MODULE_ID."/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true);

		$arrayFilePath = [
			["from" => $this->GetPath() . "/install/admin/", "to" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", "recursive" => false],
			["from" => $this->GetPath() . "/install/themes/", "to" => $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes", "recursive" => true]
		];

		foreach ($arrayFilePath as $arPath)
		{
			if ($typeAction == "install")
			{
				$this->filesInstall($arPath["from"], $arPath["to"], $arPath["recursive"]);
			}
			if ($typeAction == "uninstall")
			{
				$this->filesUninstall($arPath["from"], $arPath["to"], $arPath["recursive"]);
			}
		}
		return true;
	}
	protected function filesInstall(string $path_from, string $path_to, bool $recursive = false)
	{
		CopyDirFiles($path_from, $path_to, true, $recursive);
		return true;
	}
	protected function filesUninstall(string $path_from = "", string $path_to, bool $recursive = false)
	{
		if ($recursive)
		{
			DeleteDirFilesEx($path_to);
			return true;
		}

		DeleteDirFiles($path_from, $path_to);
		return true;
	}
}
