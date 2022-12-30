<?

class MgHpSettings
{
	static private $arFields = false;
	public $LAST_ERROR = "";

	public static function GetFields()
	{
		$arResult = array();

		if (is_array(self::$arFields))
		{
			$arResult = self::$arFields;
		}
		else
		{
			$arResult = array();

			//$cache_id = md5($class_name);
			//if( $obCache->InitCache( $cache_ttl, $cache_id, $cache_dir ) )

			$obCache = new CPHPCache;
			if ($obCache->InitCache(14400, 1, "mg.hp"))
			{
				$arResult = $obCache->GetVars();
			}
			elseif ($obCache->StartDataCache())
			{
				//				if ( defined('BX_COMP_MANAGED_CACHE') && is_object($GLOBALS['CACHE_MANAGER']) )
				//				{				
				//					global $CACHE_MANAGER;
				//					$CACHE_MANAGER->StartTagCache( "mg.hp" );
				//				}

				$arResult = self::__GetFields();


				//				if ( defined('BX_COMP_MANAGED_CACHE') && is_object($GLOBALS['CACHE_MANAGER']) )
				//				{
				//					$CACHE_MANAGER->EndTagCache( "mg.hp" );
				//				}

				$obCache->EndDataCache($arResult);
			}

			self::$arFields = $arResult;
		}

		return $arResult;
	}

	private static function __GetFields()
	{
		global $USER_FIELD_MANAGER;

		$arResult = array();

		$ID = 1;
		$entity_id = "MG_HP";

		$arUserFields = $USER_FIELD_MANAGER->GetUserFields($entity_id, $ID, LANGUAGE_ID);

		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			$arResult[$FIELD_NAME] = $arUserField['VALUE'];
		}

		return $arResult;
	}

	public static function ClearCache()
	{
		$obCache = new CPHPCache();
		$obCache->CleanDir("mg.hp");

		//BXClearCache(true, "/mg.hp/");
	}

	public function Update($arFields)
	{
		$result = true;
		global $APPLICATION;

		$this->LAST_ERROR = "";

		$ID = 1;
		$entity_id = "MG_HP";

		$APPLICATION->ResetException();
		$events = GetModuleEvents("mg.hp", "OnBeforeSettingsUpdate");
		while ($arEvent = $events->Fetch())
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, array(&$arFields));
			if ($bEventRes === false)
			{
				if ($err = $APPLICATION->GetException())
				{
					$this->LAST_ERROR .= $err->GetString();
				}
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$this->LAST_ERROR .= "Unknown error";
				}

				$result = false;
				break;
			}
		}

		if ($result)
		{
			global $USER_FIELD_MANAGER;

			// TODO: check required fields

			$USER_FIELD_MANAGER->Update($entity_id, $ID, $arFields);
			self::ClearCache();

			$events = GetModuleEvents("mg.hp", "OnAfterSettingsUpdate");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array(&$arFields));
			}
		}

		return $result;
	}
}
