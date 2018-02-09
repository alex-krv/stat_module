<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

use Vogood\Statistics\General;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Config\Option;
/**
 * Class StatisticsCalculationComponent
 */
class StatisticsCalculationComponent extends CBitrixComponent{

	public $arParams = array();
	protected $needModules = array('iblock', 'vogood.statistics');
	protected $arrFilter = array();
	


	/*
	 * Prepare parameters
	 *
	 * @param array $arParams
	 * @return array
	 */
	public function onPrepareComponentParams($arParams){

		if(!isset($arParams['CACHE_TIME']))
			$arParams['CACHE_TIME'] = 36000000;

		return $arParams;
	}


	/**
	 * Проверка на подключение модулей
	 *
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function checkModules(){
		if(!empty($this->needModules)){
			foreach($this->needModules as $module){
				if(!CModule::IncludeModule($module)){
					throw new \Bitrix\Main\LoaderException('Module '.$module.' is not found');
				}
			}
		}
	}

	/**
	 * Выполнение компонента
	 */
	public function executeComponent(){
		global $APPLICATION;
		$STORE_IBLOCK_ID = Option::get('vogood.statistics', 'storeIblockId');

		try{
			$this->checkModules();
		}catch(\Exception $e){
			return;
		}

		if( $_REQUEST['ajax'] === 'y') {
			$APPLICATION->RestartBuffer();
		}


		$fieldsName = [];
		if($this->arParams['DISPLAY_STORE_VISITS'] == 'Y'){
			$fieldsName['DISPLAY_STORE_VISITS'] = 'storeсard_visits_count';
		}
		if($this->arParams['DISPLAY_PRODUCT_VIEWS'] == 'Y'){
			$fieldsName['DISPLAY_PRODUCT_VIEWS'] = 'product_views_count';
		}
		if($this->arParams['DISPLAY_SITE_TRANSITIONS'] == 'Y'){
			$fieldsName['DISPLAY_SITE_TRANSITIONS'] = 'site_visits_count';
		}
		
		if( $_REQUEST['ajax'] === 'y') {
			$request = Application::getInstance()->getContext()->getRequest();
			$period = $request->get("period");

			if($counters = General::getPeriodList($fieldsName, $period[0]['value'], $period[1]['value'], $this->arParams['CITY_CODE'])){
				$this->arResult['COUNTER_VALUES'] = $counters;
			}
		}else{
			if($counters = General::getGeneral($fieldsName, $this->arParams['CITY_CODE'])){
				$this->arResult['COUNTER_VALUES'] = $counters;
			}
		}	
		$obCache = new \CPHPCache();
		
		$life_time = $this->arParams['CACHE_TIME'];
		$cache_id = 'store_'.$this->arParams['CITY_CODE'];
		if($obCache->InitCache($life_time, $cache_id, "/")){
			$vars = $obCache->GetVars();
			$storeName = $vars['storeName'];
		}else{
			$arStore = \CIBlockElement::GetList([], ['IBLOCK_ID' => $STORE_IBLOCK_ID, 'ID' => $this->arParams['CITY_CODE']], false, false, ['NAME', 'IBLOCK_ID', 'ID'])->Fetch();
			if($arStore){
				$this->arResult['STORE'] = $arStore;
				$storeName = htmlspecialcharsBack($arStore['NAME']);
				if($obCache->StartDataCache()){
					$obCache->EndDataCache(array(
						'storeName'    => $storeName
					));
				}
			}	
		}
		$APPLICATION->SetTitle(str_replace('#STORE#', $storeName, $this->arParams['PAGE_TITLE']));
		$this->IncludeComponentTemplate();
		
		if( $_REQUEST['ajax'] === 'y') {
			die();
		}

	}

}