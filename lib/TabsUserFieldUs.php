<?

namespace K30\Bogdo;

use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Query\Join;

class TabsUserFieldUsTable extends ORM\Data\DataManager
{
    public static function getTableName()
    {
        return 'k30_bogdo_tabs_user_field_us';
    }

    public static function getMap()
    {
        return array(
            new ORM\Fields\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true
            )),
            
			(new ORM\Fields\IntegerField('SETTINGS_ID')),
			(new ORM\Fields\Relations\Reference('SETTINGS', \Bitrix\Main\UserFieldTable::class, Join::on('this.SETTINGS_ID', 'ref.ID'))),

			(new ORM\Fields\IntegerField('ID_TABS')),
			(new ORM\Fields\Relations\Reference('TABS', \K30\Bogdo\TabsTable::class, Join::on('this.ID_TABS', 'ref.ID'))),
        );
    }
}