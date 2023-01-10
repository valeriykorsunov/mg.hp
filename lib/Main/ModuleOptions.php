<?

namespace MG\HP\Main;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class ModuleOptions
{
	static function GetPath($notDocumentRoot = false)
	{
		if ($notDocumentRoot){
			return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__,2));
		}
		else
			return dirname(__DIR__);
	}

	public static function ShowButtonNewProperty()
	{
		global $APPLICATION;

		echo '
		<a href="/bitrix/admin/userfield_edit.php?lang=<?= LANGUAGE_ID ?>&amp;ENTITY_ID=K30_BOGDO&amp;back_url=' . urlencode($APPLICATION->GetCurPageParam() . '&tabControl_active_tab=user_fields_tab') . '">Добавить настройку</a>';
	}

	public static function ShowInstructionProperty()
	{
?>
		<?= BeginNote(); ?>
		<br><strong>&lt;?echo \COption::GetOptionString( &quot;k30.bogdo&quot;, &quot;UF_PHONE&quot;);?&gt;</strong>
		<br><strong>&lt;?$email = \COption::GetOptionString( &quot;k30.bogdo&quot;, &quot;UF_EMAIL&quot;);?&gt;</strong>
		<br>D7:
		<br><strong>&lt;?echo \Bitrix\Main\Config\Option::get( &quot;k30.bogdo&quot;, &quot;UF_PHONE&quot;);?&gt;</strong>
		<br><strong>&lt;?$email = \Bitrix\Main\Config\Option::get( &quot;k30.bogdo&quot;, &quot;UF_EMAIL&quot;);?&gt;</strong>
		<?= EndNote(); ?>
	<?
	}

	public static function ShowCSS()
	{
	?>
		<style>
			table.gcustomsettings-settings-tab-headers td{
				padding: 10px;				
			}
			table.gcustomsettings-settings-tab-headers thead td {
				background-color: rgb(224, 232, 234);
				color: rgb(75, 98, 103);
				text-align: center;
				font-weight: bold;
			}

			#optionsTab table.gcustomsettings-settings-tab-headers thead td:nth-child(1) {
				min-width: 30px;
			}

			#optionsTab table.gcustomsettings-settings-tab-headers thead td:nth-child(2) {
				min-width: 200px;
			}

			.gcustomsettings-settings-tab-headers.options-tab thead td:nth-child(1) {
				min-width: 300px;
			}

			.gcustomsettings-settings-tab-headers.options-tab thead td:nth-child(2) {
				min-width: 50px;
			}

			.gcustomsettings-settings-tab-headers.options-tab thead td:nth-child(3) {
				width: 70px;
			}

			.gcustomsettings-settings-tab-headers.options-tab thead td:nth-child(4) {
				min-width: 30px;
			}

			table.gcustomsettings-settings-tab-headers td {
				border: 1px solid rgb(208, 215, 216) !important;
				/* padding: 3px !important; */
			}

			.redLink {
				color: red;
			}

			.tab-param {
				border: 1px solid rgb(208, 215, 216);
			}

			.tab-param div {
				padding: 10px;
			}
		</style>
	<?
	}

	public static function ShowJS()
	{
	?>
		<script>
			function editTab(elem) {
				// console.log(elem.getAttribute("data-tabname"));

				let popup = new BX.CDialog({
					'content_url': '<?= self::GetPath(true) ?>/ajax/formOptionTab.php',
					'content_post': 'tabname=' + elem.getAttribute("data-tabname") + '&ID_TAB=' + elem.getAttribute("data-tabid")
				});

				popup.Show();

				BX.addCustomEvent(popup, 'onWindowClose', function() {
					if (document.querySelector('#optionsTab input[name="save"]').value == "Y") {
						window.location.reload();
					}
				});
			}

			function dellTab(elem) {
				let popup = new BX.CDialog({
					'buttons': [{
							title: "Да",
							id: 'savebtn',
							name: 'savebtn',
							className: BX.browser.IsIE() && BX.browser.IsDoctype() && !BX.browser.IsIE10() ? '' : 'adm-btn-save',
							action: function() {
								let form = this.parentWindow.DIV.querySelector('form');
								let data = new FormData(form);
								fetch(form.getAttribute("action"), {
									method: 'POST',
									body: data
								})
									.then((response) => {
										return response.text();
									})
									.then((data) => {
										window.location.reload();
									});
							}
						},
						BX.CDialog.btnCancel
					],
					'width': 250,
					'height': 90,
					'content_url': '<?= self::GetPath(true) ?>/ajax/formTabDelete.php',
					'content_post': 'ID_TAB=' + elem.getAttribute("data-tabid")
				});

				popup.Show();
			}
		</script>
	<?
	}

	public static function ShowJSformOptionTab()
	{
	?>
		<?
		// если мы окном рисуем с кнопками (передаем массив кнопок), то после сабмита они исчезают.
		// Решается установкой кнопок в теле (в прилетаемом контенте) диалога:
		?>
		<script type="text/javascript">
			BX.WindowManager.Get().SetButtons([BX.CDialog.prototype.btnSave, BX.CDialog.prototype.btnCancel]);
			BX.WindowManager.Get().SetTitle("Настройки вкладки: <?= $_POST["tabname"] ?>");
		</script>
<?
	}

	public static function templateNewTab($params = array("ID" => "new", 'NAME' => '', 'SORT' => 100,))
	{

		return '
		<tr class="tab' . $params["ID"] . '">
			<td>
				' . $params["NAME"] . '
			</td>
			<td>
				' . $params["SORT"] . '
			</td>
			<td>
				<a data-tabname="' . $params["NAME"] . '" data-tabid="' . $params["ID"] . '" href="javascript:void(0)"  onclick="editTab(this);" >Изменить</a>
			</td>
			<td>
				<a class="redLink" href="javascript:void(0)" data-tabid="' . $params["ID"] . '" onclick="dellTab(this);">Удалить</a>
			</td>
		</tr>
		';
	}

	public static function GetTabsList()
	{
		$result = array();

		$Tabs = TabsTable::getEntity();
		$obTable = (new  \Bitrix\Main\ORM\Query\Query($Tabs))
			->setSelect(['*'])
			->setOrder(['SORT' => 'ASC', 'ID' => 'ASC'])
			->exec();

		$result = $obTable->fetchAll();

		return $result;
	}

	public static function GetTabsInfo($ID)
	{
		$result = array();

		$Tabs = TabsTable::getEntity();
		$obTable = (new  \Bitrix\Main\ORM\Query\Query($Tabs))
			->setFilter(["ID" => $ID])
			->setSelect(['*'])
			->exec();

		$result = $obTable->fetchAll()[0];

		return $result;
	}

	public static function AddTab(array $params = array("NAME" => "default name", "SORT" => 100))
	{
		$result = TabsTable::add(array(
			"NAME" => $params["NAME"],
			"SORT" => $params["SORT"]
		));

		if ($result->isSuccess())
		{
			return $result->getId();
		}
		else
		{
			return false;
		}
	}

	public static function updateTab($ID, array $params = array("NAME" => "default name", "SORT" => 100))
	{
		$result = TabsTable::update($ID, array(
			"NAME" => $params["NAME"],
			"SORT" => $params["SORT"]
		));

		return $result;
	}

	public static function deleteTab($ID)
	{
		$result = TabsTable::delete($ID);

		if($result->isSuccess())
		{
			$TabsUserFieldUsTable = TabsUserFieldUsTable::getEntity();
			$obTable = (new  \Bitrix\Main\ORM\Query\Query($TabsUserFieldUsTable))
				->setFilter(["ID_TABS" => $ID])
				->setSelect(['ID'])
				->exec();
			while($elem = $obTable->fetch())
			{
				TabsUserFieldUsTable::delete($elem["ID"]);
			}			
		}
	}

	public static function GetUserFieldList($ID_TAB)
	{
		$result = array();

		$UserFieldTabsEntity = \Bitrix\Main\UserFieldTable::getEntity();
		$UserFieldTabsEntity->addField(
			(new Reference(
				"TABS",
				TabsUserFieldUsTable::getEntity(),
				// Join::on(
				// 	'this.ID','ref.SETTINGS_ID'
				// )
				(new \Bitrix\Main\ORM\Query\Filter\ConditionTree)
				->whereColumn('this.ID','ref.SETTINGS_ID')
				->where('ref.ID_TABS',"=",$ID_TAB)
				))
				->configureJoinType('left')
		);

		$UserFieldTabsEntity->addField(
			(new Reference(
				"USER_FIELD_LANG",
				\Bitrix\Main\UserFieldLangTable::getEntity(),
				(new \Bitrix\Main\ORM\Query\Filter\ConditionTree)
					->whereColumn('this.ID','ref.USER_FIELD_ID')
					->where('ref.LANGUAGE_ID',"=",'ru')
				))
				->configureJoinType('left')
		);

		$obTable = (new  \Bitrix\Main\ORM\Query\Query($UserFieldTabsEntity))
			->setSelect(['ID','FIELD_NAME', 'SETTINGS_ID'=>'TABS.SETTINGS_ID', 'USER_FIELD_NAME'=>'USER_FIELD_LANG.EDIT_FORM_LABEL'])
			->setFilter([
				"ENTITY_ID" => "K30_BOGDO"
			])
			->setOrder(['SORT' => 'ASC', 'ID' => 'ASC'])
			->exec();

		$result = $obTable->fetchAll();

		return $result;
	}

	public static function UpdateUserFieldListforTab($ID_TAB, array $ID_OPTION)
	{
		// добавить 
		$TabsUserFieldUsTable = TabsUserFieldUsTable::getEntity();
		$obTable = (new  \Bitrix\Main\ORM\Query\Query($TabsUserFieldUsTable))
			->setFilter(["ID_TABS" => $ID_TAB])
			->setSelect(['*'])
			->exec();

		$arrayTabsUserFieldUsTable = $obTable->fetchAll();

		global $arTabUFT;
		$arTabUFT = $arrayTabsUserFieldUsTable;

		$arrayAdd = array_filter(
			$ID_OPTION,
			function($v,$k){
				global $arTabUFT;
				if(in_array($k,array_column($arTabUFT,"SETTINGS_ID")) or $v!="Y")
				{
					return false;
				}
				return true;
			},
			ARRAY_FILTER_USE_BOTH
		);
		foreach ($arrayAdd as $k=>$v)
		{
			$addMulty[] = [
				"SETTINGS_ID" => $k,
				"ID_TABS" => $ID_TAB
			];
		}
		if($addMulty)
		{
			TabsUserFieldUsTable::addMulti($addMulty);
		}
		
		// удалить
		foreach ($arTabUFT as $option) 
		{
			if(in_array($option["SETTINGS_ID"],array_keys($ID_OPTION)) and $ID_OPTION[$option["SETTINGS_ID"]] != "Y")
			{
				// echo"<pre>"; var_dump($option["ID"]); echo "</pre>";
				TabsUserFieldUsTable::delete($option["ID"]);
			}
		}
	}

	public static function GetTabAndUserFieldCode($ID_TAB)
	{
		$TabAndUserFieldCode = TabsUserFieldUsTable::getEntity();
		$obTable = (new  \Bitrix\Main\ORM\Query\Query($TabAndUserFieldCode))
			->setFilter(["ID_TABS" => $ID_TAB])
			->setSelect(['*', 'FIELD_NAME'=>'SETTINGS.FIELD_NAME', 'SORT'=>'SETTINGS.SORT'])
			->setOrder(['SORT' => 'ASC'])
			->exec();

			return $obTable->fetchAll();
	}
}
