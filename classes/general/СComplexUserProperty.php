<?

use Bitrix\Main\UserField\Types\StringType;

class СComplexUserProperty extends StringType
{
	public static function getDescription(): array
	{
		return [
			'USER_TYPE_ID' => 'idComplexProp',
			'CLASS_NAME' => 'СComplexUserProperty',
			'DESCRIPTION' => "Комплексное свойство ..",
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_STRING,
		];

		// return array(
		// 	'USER_TYPE_ID' => 'idStore',
		// 	'CLASS_NAME' => '\Askaron\Fields\Store',
		// 	'DESCRIPTION' => GetMessage('ASKARON_FIELDS_STORE_DESCRIPTION'),
		// 	'BASE_TYPE' => 'int',
		// );
	}
}