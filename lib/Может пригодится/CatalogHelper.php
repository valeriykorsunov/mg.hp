<?php

namespace MG\HP\Site;

class CatalogHelper
{
	
	/*
	 * Получить описание элементов из списка свойств элемента в каталоге
	 *
	 * @param array $arElementsID - список ID элементов
	 */
	public static function GetElementsDescription(array $arElementsID, int $countPropsInDesc = 7): array
	{
		$result = [];
		\Bitrix\Main\Loader::includeModule('iblock');
		$subQuery = \Bitrix\Iblock\SectionPropertyTable::query()
			->setSelect(['PROPERTY_ID'])
			->where('IBLOCK_ID', CATALOG_IBLOCK_ID)
			->where('SMART_FILTER', 'Y');
		$arrIblockProps = \Bitrix\Iblock\PropertyTable::query()
			->where('IBLOCK_ID', CATALOG_IBLOCK_ID)
			->whereIn('ID', $subQuery)
			->setSelect(["ID"])
			->exec()
			->fetchAll();
		$arIdPropSmartFilter = is_array($arrIblockProps) ? array_column($arrIblockProps, "ID") : [];
		
		$res = CIBlockElement::GetList(
			arFilter: [
				'IBLOCK_ID' => CATALOG_IBLOCK_ID,
				'IBLOCK_TYPE' => CATALOG_IBLOCK_TYPE,
				'ID' => $arElementsID
			],
			arSelectFields: [
				'ID',
				'IBLOCK_ID'
			]
		);
		
		while ($ob = $res->GetNextElement(false, false)) {
			$fields = $ob->GetFields();
			$properties = $ob->GetProperties(["SORT" => "ASC"]);
			$i = 0;
			// собираем свойства используемые в фильтре
			foreach ($properties as $key => $val) {
				if (!self::CheckPropForElementDescription($val)) {
					unset($properties[$key]);
					continue;
				}
				
				if (in_array($val["ID"], $arIdPropSmartFilter)) {
					$result[$fields["ID"]]["PREVIEW_PROPERTY_TEXT"] .= $val['NAME'] . ':  ' . $val['VALUE'] . '<br>';
				}
				if ($countPropsInDesc <= ++$i) {
					$result[$fields["ID"]]["PREVIEW_PROPERTY_TEXT"] .= $result['ITEMS'][$fields["ID"]]["PREVIEW_PROPERTY_TEXT"] ? '...' : '';
					break;
				}
			}
			// если не хватило добавляем остальными свойствами
			foreach ($properties as $key => $val) {
				if (!self::CheckPropForElementDescription($val)) {
					unset($properties[$key]);
					continue;
				}
				if (!in_array($val["ID"], $arIdPropSmartFilter)) {
					$result[$fields["ID"]]["PREVIEW_PROPERTY_TEXT"] .= $val['NAME'] . ':  ' . $val['VALUE'] . '<br>';
				}
				if ($countPropsInDesc <= ++$i) {
					$result[$fields["ID"]]["PREVIEW_PROPERTY_TEXT"] .= $result['ITEMS'][$fields["ID"]]["PREVIEW_PROPERTY_TEXT"] ? '...' : '';
					break;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Проверка свойства на соответствия требованиям добавления в список
	 * По задаче:
	 * - В список характеристик должны попадать в первую очередь поля применяемые в умном фильтре
	 * - в списке отображаются только заполненные поля
	 * - не выводить поля используя список исключений
	 */
	private static function CheckPropForElementDescription(array $arProp): bool
	{
		$arrNotShowCodes = \Bitrix\Main\Config\Option::get("mg.hp", "UF_PROP_EXCEPTION");
		if (
			is_array($arProp['VALUE'])
			|| mb_strlen(trim($arProp['VALUE'])) == 0
			|| mb_substr($arProp['NAME'], 0, 1) == '#'
			|| in_array($arProp['ID'], $arrNotShowCodes)
		) {
			return false;
		}
		
		return true;
	}
}
