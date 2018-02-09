<?require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_before.php");

define('ADMIN_MODULE_NAME', 'vogood.statistics');

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Vogood\Statistics\StatTable;
use Bitrix\Main\Type;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;


Loc::loadMessages(__FILE__);

if(!Loader::includeModule(ADMIN_MODULE_NAME) || !Loader::includeModule('iblock'))
{
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
    ShowError(Loc::getMessage(''));
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
?>

<?
$CATALOG_IBLOCK_ID = Option::get(ADMIN_MODULE_NAME, 'catalogIblockId');
$BRAND_IBLOCK_ID = Option::get(ADMIN_MODULE_NAME, 'brandIblockId');

$request = Application::getInstance()->getContext()->getRequest();
$storeName = $request->get("STORE_NAME");
$APPLICATION->SetTitle(Loc::getMessage('VO_STATISTICS_PAGE_TITLE', ['#STORE#' => $storeName]));

$sTableID = StatTable::getTableName();
$arEntiyFields = StatTable::getEntity()->getFields();

$arHeadFields = array(

    'date' => array(
        'sort'		=> '',
        'default'	=> true
    ),
    'product_views_count' => array(
        'sort'		=> '',
        'default'	=> true
    ),
    'product_viewing_totaltime' => array(
        'sort'		=> '',
        'default'	=> true
    ),
    'storeсard_visits_count' => array(
        'sort'		=> '',
        'default'	=> true
    ),
    'product_views_count_from_store' => array(
        'sort'		=> '',
        'default'	=> true
    ),
    'product_views_count_from_catalog' => array(
        'sort'		=> '',
        'default'	=> true
    ),
    'site_visits_count' => array(
        'sort'		=> '',
        'default'	=> true
    )
);

$oSort = new CAdminSorting($sTableID, 'id', 'ASC'); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort);

// Элементы фильтра
$arFilterFields = array(
    'find_timestamp_1',
    'find_timestamp_2',
    'find_store_id',
);

// Инициализируем фильтр
$lAdmin->InitFilter($arFilterFields);
function CheckFilter($FilterArr)
{
    global $strError;
    foreach($FilterArr as $f)
        global $$f;

    $str = "";
    if(strlen(trim($find_timestamp_1))>0 || strlen(trim($find_timestamp_2))>0)
    {
        $date_1_ok = false;
        $date1_stm = MkDateTime(FmtDate($find_timestamp_1,"D.M.Y"),"d.m.Y");
        $date2_stm = MkDateTime(FmtDate($find_timestamp_2,"D.M.Y")." 23:59","d.m.Y H:i");
        if (!$date1_stm && strlen(trim($find_timestamp_1))>0)
            $str.= GetMessage("MAIN_WRONG_TIMESTAMP_FROM")."<br>";
        else $date_1_ok = true;
        if (!$date2_stm && strlen(trim($find_timestamp_2))>0)
            $str.= GetMessage("MAIN_WRONG_TIMESTAMP_TILL")."<br>";
        elseif ($date_1_ok && $date2_stm <= $date1_stm && strlen($date2_stm)>0)
            $str.= GetMessage("MAIN_FROM_TILL_TIMESTAMP")."<br>";
    }

    $strError .= $str;
    if(strlen($str)>0)
    {
        global $lAdmin;
        $lAdmin->AddFilterError($str);
        return false;
    }

    return true;
}


// Если все значения фильтра корректны, обработаем его
$arFilter = array();
$storeId = $request->get("STORE_ID");

if(CheckFilter($arFilterFields))
{
    if($find_timestamp_1){
        $arFilter['>=date'] = new Type\DateTime($find_timestamp_1, "d.m.Y");
    }
    if($find_timestamp_2){
        $arFilter['<=date'] = new Type\DateTime($find_timestamp_2, "d.m.Y");
    }
    if(!empty($storeId)){
        $arFilter['=store_id'] = $storeId;
    }
}


$arHeaders = array();
foreach($arHeadFields as $fieldCode=>$arField){
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

//$arChannelTypes = StatTable::getTypes();

$arData = array();
$rsData = StatTable::getList(array(
    'select'	=> array('*'),
    'order'		=> array($by => $order),
    'filter'	=> $arFilter,
));

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(''));

$lAdmin->AddHeaders($arHeaders);
if(!empty($dateFrom) && !empty($dateTo)){
	$arTotal = [
		'id' => 0,
		'date'	=> 0,
		'product_views_count'	=> 0,
		'product_viewing_totaltime'	=> 0,
		'storeсard_visits_count'	=> 0,
		'product_views_count_from_store'	=> 0,
		'product_views_count_from_catalog'	=> 0,
		'site_visits_count'	=> 0
	];
}

$dateFrom = $request->get("find_timestamp_1");
$dateTo = $request->get("find_timestamp_2");

while($arDataRow = $rsData->fetch())
{
	if(!empty($dateFrom) && !empty($dateTo)){
		$arTotal['id'] = $arDataRow['id'];
		$arTotal['date'] = $arDataRow['date'];
		$arTotal['product_views_count'] += $arDataRow['product_views_count'];
		$arTotal['product_viewing_totaltime'] += $arDataRow['product_viewing_totaltime'];
		$arTotal['storeсard_visits_count'] += $arDataRow['storeсard_visits_count'];
		$arTotal['product_views_count_from_store'] += $arDataRow['product_views_count_from_store'];
		$arTotal['product_views_count_from_catalog'] += $arDataRow['product_views_count_from_catalog'];
		$arTotal['site_visits_count'] += $arDataRow['site_visits_count'];
	}else{
		$row = &$lAdmin->AddRow($arDataRow["id"], $arDataRow, '', '');
		$row->AddViewField('date', $row->arRes['date']);
		$row->AddViewField('product_views_count', $row->arRes['product_views_count']);
		$row->AddViewField('product_viewing_totaltime', $row->arRes['product_viewing_totaltime']);
		$row->AddViewField('storeсard_visits_count', $row->arRes['storeсard_visits_count']);
		$row->AddViewField('product_views_count_from_store', $row->arRes['product_views_count_from_store']);
		$row->AddViewField('product_views_count_from_catalog', $row->arRes['product_views_count_from_catalog']);
		$row->AddViewField('site_visits_count', $row->arRes['site_visits_count']);
	}
	
}

if(isset($arTotal)){
	if(empty($arTotal['date']) && !empty($dateTo))
		$arTotal['date'] = $dateTo;

	$row = &$lAdmin->AddRow($arTotal["id"], $arTotal, '', '');
	$row->AddViewField('date', $row->arRes['date']);
	$row->AddViewField('product_views_count', $row->arRes['product_views_count']);
	$row->AddViewField('product_viewing_totaltime', $row->arRes['product_viewing_totaltime']);
	$row->AddViewField('storeсard_visits_count', $row->arRes['storeсard_visits_count']);
	$row->AddViewField('product_views_count_from_store', $row->arRes['product_views_count_from_store']);
	$row->AddViewField('product_views_count_from_catalog', $row->arRes['product_views_count_from_catalog']);
	$row->AddViewField('site_visits_count', $row->arRes['site_visits_count']);
}
	

if(!empty($storeId)){
	
    $totalCount = 0;
    $arBrans = [];
    $arSelect = ['IBLOCK_ID', 'ID', 'IBLOCK_SECTION_ID', 'PROPERTY_BRAND'];
    $products = CIBlockElement::GetList(Array(), ['IBLOCK_ID' => $CATALOG_IBLOCK_ID, 'PROPERTY_ATTACH_SHOPS.ID' => $storeId], false, false, $arSelect);
    while($arProductInfo = $products->fetch()){
        $totalCount++;
        $arProducts[] = $arProductInfo;
    }
    
    foreach($arProducts as $key => $prod){
    	if(!isset($arBrans[$prod['PROPERTY_BRAND_VALUE']][$prod['IBLOCK_SECTION_ID']]))
    		$arBrans[$prod['PROPERTY_BRAND_VALUE']][$prod['IBLOCK_SECTION_ID']] = 0;
        $arBrans[$prod['PROPERTY_BRAND_VALUE']][$prod['IBLOCK_SECTION_ID']] += 1;
    }

    if(!empty($arBrans)){
        foreach($arBrans as $id => $arSects){
            $arId[] = $id;
            foreach ($arSects as $key => $value) {
            	if (!in_array($key, $arIdSect))
            		$arIdSect[] = $key;
            }
        }

        $brands = CIBlockElement::GetList(Array(), ['IBLOCK_ID' => $BRAND_IBLOCK_ID, 'ID' => $arId], false, false, ['IBLOCK_ID', 'ID', 'NAME']);
        while($arBrands = $brands->fetch()){
            $brandNames[$arBrands['ID']] = $arBrands['NAME'];
        }

        $sections = CIBlockSection::GetList(Array(), ['IBLOCK_ID' => $CATALOG_IBLOCK_ID, 'ID' => $arIdSect], false, ['IBLOCK_ID', 'ID', 'NAME'], false);
        while($arSects = $sections->fetch()){
            $sectNames[$arSects['ID']] = $arSects['NAME'];
        }
        unset($arId, $arIdSect, $arSects);
    }
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
    <div class="stat-wrap">
        <div class="stat-elem">
            <div class="elem-title"><?=Loc::getMessage("VO_STATISTICS_BRAND_PROD_TITLE")?></div>
            <div class="elem-content">
                <div class="content-list">
                	<?if(empty($arBrans)):?>
                		<div><?=Loc::getMessage("VO_STATISTICS_EMPTY_BRANDS")?></div>
            		<?else:?>
            			<?foreach($arBrans as $brand => $arSects):?>
		                    <div>
		                        <?=$brandNames[$brand]?>:
		                        <ul>
		                        	<?foreach($arSects as $sect => $value):?>
		                        		<li><?=$sectNames[$sect]?> — <?=$value?></li>
		                        	<?endforeach;?>
		                        </ul>
		                    </div>
	                    <?endforeach;?>
            		<?endif;?>
                </div>
            </div>
        </div>
        
        <div class="stat-elem">
            <div class="elem-title"><?=Loc::getMessage("VO_STATISTICS_TOTAL_CAUNT_TITLE")?> - <?=$totalCount?></div>
        </div>
    </div>
    <form name="find_form" method="GET" action="<?=$APPLICATION->GetCurUri("STORE_ID=$storeId&STORE_NAME=$storeName")?>?">
        <?
        $oFilter = new CAdminFilter(
            $sTableID."_filter",
            false,
            array(
                Loc::getMessage('VO_STATISTICS_FIND_STORE'),
                Loc::getMessage('VO_STATISTICS_FIND_BY_TIME_INTERVAL')
            )
        );
        $oFilter->Begin();
        ?>
        <tr>
            <td><?=Loc::getMessage('VO_STATISTICS_FIND_BY_TIME_INTERVAL')?></td>
            <td><?echo CalendarPeriod('find_timestamp_1', htmlspecialcharsbx($find_timestamp_1), 'find_timestamp_2', htmlspecialcharsbx($find_timestamp_2), 'find_form', 'Y')?></td>
        </tr>
        <?
        $oFilter->Buttons(array('table_id' => $sTableID, 'url' => $APPLICATION->GetCurUri("STORE_ID=$storeId&STORE_NAME=$storeName"), 'form' => 'find_form'));
        $oFilter->End();
        ?>
    </form>
<?
$lAdmin->DisplayList();
?>
<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>