<?require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_before.php");

define('ADMIN_MODULE_NAME', 'vogood.statistics');

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Vogood\Statistics\vogood_statistics;
use Bitrix\Main\Type;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Config\Option;


Loc::loadMessages(__FILE__);

if(!Loader::includeModule(ADMIN_MODULE_NAME) || !Loader::includeModule('iblock'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage(''));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
$APPLICATION->SetTitle(Loc::getMessage('VO_STATISTICS_PAGE_TITLE'));
?>

<?
$MODULE_ID = 'vogood.statistics';
$sTableID = ElementTable::getTableName();
$arEntiyFields = ElementTable::getEntity()->getFields();
$storeIblockId = Option::get($MODULE_ID, 'storeIblockId');

$arHeadFields = array(
	'ID' => array(
		'sort'		=> '',
		'default'	=> true
	),
	'NAME' => array(
		'sort'		=> '',
		'default'	=> true
	)
);
$oSort = new CAdminSorting($sTableID, 'ID', 'ASC'); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort);

// Элементы фильтра
$arFilterFields = array(
    'find_store_name',
);
// Инициализируем фильтр
$lAdmin->InitFilter($arFilterFields);
function CheckFilter($FilterArr){
    foreach($FilterArr as $f)
        global $$f;

    return true;
}
// Если все значения фильтра корректны, обработаем его
$arFilter = array(
	'=IBLOCK_ID' => $storeIblockId
);

if(CheckFilter($arFilterFields)){
	if(!empty($find_store_name)){
		$arFilter['%NAME'] = $find_store_name; 
	}
}


$arHeaders = array();

foreach($arHeadFields as $fieldCode=>$arField) {
	if(isset($arEntiyFields[$fieldCode])){
        $objField = $arEntiyFields[$fieldCode];
        $arHeaders[] = array(
            'id'		=> $objField->getName(),
            'content'	=> $objField->getTitle(),
            'sort'		=> ($arField['sort'] === false || $arField['sort'] ? $arField['sort'] : $objField->getName()),
            'default'	=> $arField['default']
        );
    }
}

$arData = array();
$rsData = ElementTable::getList(array(
    'select'	=> array('*'),
    'order'		=> array($by => $order),
    'filter'	=> $arFilter,
));

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart(40);

$lAdmin->NavText($rsData->GetNavPrint(''));

$lAdmin->AddHeaders($arHeaders);

while($arDataRow = $rsData->fetch())
{
	$arActions = array();

	$row = &$lAdmin->AddRow($arDataRow['ID'], $arDataRow, '', '');
	$row->AddViewField('NAME', $row->arRes['NAME']);

	$arActions[] = array(
		'ICON'		=> '',
		'TEXT'		=> 'Посмотреть статистику',
		'DEFAULT'	=> true,
		'ACTION'	=> $lAdmin->ActionRedirect('vogood.statistics_channel_detail.php?STORE_ID='.$row->arRes['ID'].'&STORE_NAME='.htmlspecialchars($row->arRes['NAME'])),
		'ONCLICK'	=> '',
	);
	$arActions[] = array('SEPARATOR' => 'Y');


	if(!empty($arActions))
		$row->AddActions($arActions);

}


$lAdmin->CheckListMode();



// резюме таблицы
$lAdmin->AddFooter(
	array(
		array('title' => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"), 'value' => $rsData->SelectedRowsCount()), // кол-во элементов
		array('counter' => true, 'title' => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"), 'value' => '0'), // счетчик выбранных элементов
	)
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
    <form name="find_form" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
        <?
        $oFilter = new CAdminFilter(
            $sTableID."_filter",
            false,
            array(
                Loc::getMessage('VO_STATISTICS_FIND_STORE_BY_NAME')
            )
        );
        $oFilter->Begin();
        ?>
        <tr>
            <td><?=Loc::getMessage('VO_STATISTICS_FIND_STORE_BY_NAME')?></td>
            <td><input type="text" size="25" name="find_store_name" value=""></td>
        </tr>
        <?
        $oFilter->Buttons(array('table_id' => $sTableID,'url' => $APPLICATION->GetCurPage(), 'form' => 'find_form'));
        $oFilter->End();
        ?>
    </form>

<?
$lAdmin->DisplayList();
?>
<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>