<?php

namespace MG\HP\Site;

use Bitrix\Main\UserField\Types\BaseType;

class CCustomPropetyListForIB extends BaseType
{
	private static $showedCss = false;
	private static $showedJs = false;
	
	public static function getUserTypeDescription(): array
	{
		return array(
			"USER_TYPE_ID" => "propertyList.IB",
			"CLASS_NAME" => __CLASS__,
			'DESCRIPTION' => "Список свойств Инфоблока (HELPER)",
			'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_STRING,
		);
	}
	
	
	public static function getDbColumnType(): string
	{
		return 'text';
	}
	
	/**
	 * @param array $userField
	 * @param array|null $additionalParameters
	 * @return string
	 */
	// public function GetEditFormHTML($arProperty, $strHTMLControlName)
	public static function getEditFormHtml(array $userField, ?array $additionalParameters): string
	{
		if (!empty($userField['SETTINGS'])) {
			$arFields = $userField['SETTINGS'];
		} else {
			return '<span>Не заполнен список полей в настройках свойства </span>';
		}
		
		$result = "";
		$arrIblockProps = \Bitrix\Iblock\PropertyTable::query()
			->addFilter("IBLOCK_ID", CATALOG_IBLOCK_ID)
			->setSelect(["ID", "NAME"])
			->setCacheTtl("36000")
			->exec()->fetchAll();
		$PROP_ID = $additionalParameters['VALUE'];
		$result .= '<select class="inp-code" name="' . $additionalParameters['NAME'] . '[PROP_ID]">
							<option value="">Не выбрано</option>;';
		foreach ($arrIblockProps as $prop) {
			$result .= '<option value="' . $prop['ID'] . '" ';
			if ($PROP_ID === $prop['ID']) {
				$result .= 'selected';
			}
			$result .= '>' . $prop['NAME'] . '</option>';
		}
		$result .= '</select>';
		
		return $result;
	}
	
	public static function OnBeforeSave($arProperty, $arValue)
	{
		if (!empty($arValue)) {
			$arResult = $arValue["PROP_ID"];
		} else {
			$arResult = '';
		}
		
		return $arResult;
	}
	
	/**
	 * @param array|bool $userField
	 * @param array|null $additionalParameters
	 * @param $varsFromForm
	 * @return string
	 */
	// public function GetSettingsHTML($arProperty = false, $strHTMLControlName, $arHtmlControl)
	public static function getSettingsHtml($userField, ?array $additionalParameters, $varsFromForm): string
	{
		CModule::IncludeModule("iblock");
		$resIB = CIBlock::GetList();
		$result = '<tr><td colspan="2" align="center">';
		
		$result .= 'Выберите инфоблок: <select name="'.$additionalParameters["NAME"].'[IBLOCK_ID]" id="IBLOCK_ID">';
		$result .= '<option value="0">Не выбрано</option>';
		while ($ob = $resIB->GetNext()) {
			$selected =  $userField['SETTINGS']["IBLOCK_ID"] == $ob['ID']? 'selected' : '';
			$result .= '<option ' . $selected .'  value="' . $ob['ID'] . '">' . $ob['NAME'] . ' - [' .$ob['ID'] .']</option>';
		}
		$result .= '</select>';
		
		$result .= '</td></tr>';
		
		return $result;
	}
	
	public static function PrepareSettings($arUserField)
	{
		$result = [];
		if (!empty($arUserField['SETTINGS'])) {
			foreach ($arUserField['SETTINGS'] as $code => $value) {
				$result[$code] = $value;
			}
		}
		return $result;
	}
}
