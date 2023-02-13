<?

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
class mg_hp extends CModule
{
	public $NAME_DIRECTORY;

    function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__ . '/version.php');

        $this->MODULE_ID = 'mg.hp';
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = "HELPER";
        $this->MODULE_DESCRIPTION = "HELPER";

        $this->PARTNER_NAME = "MG";
        $this->PARTNER_URI = "http://bxwork.ru";

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
            unset($eventManager);
            ModuleManager::registerModule($this->MODULE_ID);

            Loader::includeModule($this->MODULE_ID);
            $this->InstallDB();
            $this->editHandler("install");
            $this->editFiles("install");
        }
        else
        {
            $APPLICATION->ThrowException('Версия ядра Битрикс не поддерживается или версия php ниже необходимой.');
        }

        $APPLICATION->IncludeAdminFile("INSTALL TITLE", $this->GetPath() . "/install/step.php");
    }

    function DoUninstall()
    {
        if (!check_bitrix_sessid()) return false;
        global $APPLICATION;
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        if ($request["step"] < 2)
        {
            $APPLICATION->IncludeAdminFile("UNINSTALL TITLE", $this->GetPath() . "/install/unstep1.php");
        }
        elseif ($request["step"] == 2)
        {
            if ($request["savedata"] != "Y")
            {
                $this->UnInstallDB();
            }

            $this->editHandler("uninstall");
            $this->editFiles("uninstall");
			
            unset($eventManager);
            ModuleManager::unRegisterModule($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile("UNINSTALL TITLE", $this->GetPath() . "/install/unstep2.php");
        }
    }

    function InstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        if(!Application::getConnection(\MG\HP\Main\TabsTable::getConnectionName())->isTableExists(Base::getInstance('\MG\HP\Main\TabsTable')->getDBTableName()))
        {
            Base::getInstance('\MG\HP\Main\TabsTable')->createDbTable();
        }

        if(!Application::getConnection(\MG\HP\Main\TabsUserFieldUsTable::getConnectionName())->isTableExists(Base::getInstance('\MG\HP\Main\TabsUserFieldUsTable')->getDBTableName()))
        {
            Base::getInstance('\MG\HP\Main\TabsUserFieldUsTable')->createDbTable();
        }

    }

    function UnInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        Application::getConnection(\MG\HP\Main\TabsTable::getConnectionName())->queryExecute('drop table if exists '.Base::getInstance('\MG\HP\Main\TabsTable')->getDBTableName());

        Application::getConnection(\MG\HP\Main\TabsUserFieldUsTable::getConnectionName())->queryExecute('drop table if exists '.Base::getInstance('\MG\HP\Main\TabsUserFieldUsTable')->getDBTableName());

    }

    protected function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion("main"), "20.00.00");
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
        $listHandler = array(
            ["ModuleId" => "main", "Event" => "onPageStart", "EventHandler"=> 'MG\HP\Main\EventHandler', "Sort" => "100"],
            ["ModuleId" => "main", "Event" => "OnEpilog", "EventHandler"=> 'MG\HP\Main\EventHandler', "Sort" => "100"],
            ["ModuleId" => "main", "Event" => "OnUserTypeBuildList", "EventHandler"=> 'MG\HP\Main\CComplexUserProperty', "to_method"=> "getUserTypeDescription", "Sort" => "100"],
            ["ModuleId" => "main", "Event" => "OnUserTypeBuildList", "EventHandler"=> 'MG\HP\Main\CCustomTypeHtml', "to_method"=> "GetUserTypeDescription", "Sort" => "100"],
        );

        foreach ($listHandler as $params)
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
            $params["EventHandler"],
            $params["to_method"]?? $params["Event"],
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
            $params["EventHandler"],
            $params["to_method"]?? $params["Event"]
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
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/".$this->NAME_DIRECTORY."/modules/".$this->MODULE_ID."/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true);

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
