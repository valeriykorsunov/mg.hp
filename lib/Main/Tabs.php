<?
namespace MG\HP\Main;

use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Query\Join;

class TabsTable extends ORM\Data\DataManager
{
	public static function getTableName()
    {
        return 'mg_hp_tabs';
    }

	public static function getMap()
	{
		return array(
			//ID
            new ORM\Fields\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true
            )),
            //Название
			new ORM\Fields\StringField('NAME', array(
				'required' => true,
			)),
			// сортировка
			new ORM\Fields\IntegerField('SORT',array(
				'default_value' => 100
			))
        );
	}
}