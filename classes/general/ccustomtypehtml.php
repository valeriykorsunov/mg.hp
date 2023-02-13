<?

use \Bitrix\Main,
    \Bitrix\Main\UserField\Types\StringType,
    \Bitrix\Fileman;

class CCustomTypeHtml extends StringType
{
    public static function GetUserTypeDescription(): array
	{
		return array(
			"USER_TYPE_ID" => "customhtmltext",
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => "Пользовательское свойство Текст/Html",
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_STRING,
		);
	}

    static function GetEditFormHTML(array $userField, ?array $additionalParameters): string
	{
		if ($userField["ENTITY_VALUE_ID"] < 1 && strlen($userField["SETTINGS"]["DEFAULT_VALUE"]) > 0)
            $additionalParameters["VALUE"] = htmlspecialcharsbx($userField["SETTINGS"]["DEFAULT_VALUE"]);
		if ($userField["SETTINGS"]["ROWS"] < 10)
			$arUserField["SETTINGS"]["ROWS"] = 10;

		if ($userField['MULTIPLE'] == 'Y')
			$name = preg_replace("/[\[\]]/i", "_", $additionalParameters["NAME"]);
		else
			$name = $additionalParameters["NAME"];

		ob_start();

		\CFileMan::AddHTMLEditorFrame(
			$name,
            $additionalParameters["VALUE"],
			$name . "_TYPE",
			strlen($additionalParameters["VALUE"]) ? "html" : "text",
			array(
				'height' => $userField['SETTINGS']['ROWS'] * 10,
			)
		);

		if ($userField['MULTIPLE'] == 'Y')
			echo '<input type="hidden" name="' . $additionalParameters["NAME"] . '" >';

		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	function OnBeforeSave($userField, $value)
    {
        if (!$value)
        {
            return null;
        }

		if ($userField['MULTIPLE'] == 'Y')
		{
			foreach ($_POST as $key => $val)
			{
				if ( preg_match("/" . $userField['FIELD_NAME'] . "_([0-9]+)_$/i", $key, $m) )
				{
					$value = $val;
					unset($_POST[$key]);
					break;
				}
			}
		}
        return $value;
    }
}

