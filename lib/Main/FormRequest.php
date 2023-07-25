<?

namespace MG\HP\Main;

class FormRequest
{
	public $fields = [];

	function __construct()
	{
	}

	function sendMailBx($message_id = 7, $fields)
	{
		if (!\CModule::IncludeModule("iblock")) exit;
		$fields = array_merge(array("EMAIL_TO" => \COption::GetOptionString('main', 'email_from')), $fields);
		$event = 'FEEDBACK_FORM';
		$site_id = SITE_ID;

		// +файл
		$fileID = \CIBlockElement::GetProperty($this->IBLOCK_ID, $fields["ID"], array("sort" => "asc"), array("CODE" => "FILE"))->Fetch();
		$addField = array($fileID["VALUE"]);

		$result = \CEvent::SendImmediate($event, $site_id, $fields, '', $message_id, $addField);

		return $result;
	}
	function addSendToIB($IBLOCK_ID, $arFields = array())
	{
		if (!\CModule::IncludeModule("iblock")) exit;

		$name = ($arFields["NAME"] ?: "NoName");
		$arFields = array_merge(
			array(
				"IBLOCK_ID" => $IBLOCK_ID,
				"NAME" => $name,
				"ACTIVE" => "Y",
				"PREVIEW_TEXT" => ""
			),
			$arFields
		);

		if ($_FILES) {
			$arFields["PROPERTY_VALUES"]["FILE"] = array(
				"name" => $_FILES["file"]['name'], // имя файла, как оно будет в письме
				"size" => $_FILES["file"]['size'], // работает и без указания размера
				"tmp_name" => $_FILES["file"]['tmp_name'], // собственно файл
				// "type" => "",                    
				"old_file" => "0", // ID "старого" файла
				"del" => "N", // удалять прошлый?
				"MODULE_ID" => "", // имя модуля, работает и так
				"description" => "", // описание
			);
		}
		
		$el = new \CIBlockElement;
		$PRODUCT_ID = $el->Add(
			$arFields,
			false,
			false,
			false
		);

		return $PRODUCT_ID;
	}

	function jsonResponse(array $data)
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
			header('Access-Control-Allow-Origin: *');
			header("Content-type: application/json; charset=utf-8");
			echo json_encode($data);
		exit;
	}
}
