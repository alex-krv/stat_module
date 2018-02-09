<?
namespace Vogood\Statistics;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Iblock\ElementTable;

class General {
	private static $MODULE_ID = 'vogood.statistics';

	/**
	 * Получает счетчики магазина за текущий день
	 *
	 * @param $fieldsName
	 * @param $storeID
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getCountersList($fieldsName, $storeID){
		if(empty($fieldsName) || empty($storeID))
			return false;

		$unixTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

		$arSelect[] = 'id';

		foreach($fieldsName as $field){
			$arSelect[] = $field;
		}

		$db = \Vogood\Statistics\StatTable::getList(array
			(
				'select'	=> $arSelect,
				'filter'	=> array
				(
					'=date'	=> new Type\DateTime(ConvertTimeStamp($unixTime, "FULL")),
					'store_id' => (int)$storeID
				)
			)
		)->fetchAll();

		return $db;
	}

	/**
	 * Получает счетчики магазина за все время
	 *
	 * @param $fieldsName
	 * @param $storeID
	 * @return array|bool
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getGeneral($fieldsName, $storeID){
		if(empty($fieldsName) || empty($storeID))
			return false;

		$queryBuilder = new Entity\Query(\Vogood\Statistics\StatTable::getEntity());
					
		foreach($fieldsName as $field){
			$sumFieldName = $field . '_sum';
			$queryBuilder -> registerRuntimeField($sumFieldName, array(
						"data_type" => 'integer',
						"expression" => array('SUM(%s)', $field)
					));
			
			$arSelect[] = $sumFieldName;
			$arGroup[] = $field;

		};
		$queryBuilder->setSelect($arSelect);
		$queryBuilder->setFilter(array('store_id' => (int)$storeID));

		return $queryBuilder->exec()->fetchAll();
	}

	/**
	 * Получает счетчики из временного диапазона
	 *
	 * @param $fieldsName
	 * @param $from
	 * @param $to
	 * @param $storeID
	 * @return array|bool
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getPeriodList($fieldsName, $from, $to, $storeID){
		if(empty($fieldsName) || empty($storeID))
			return false;

		$arSelect[] = 'id';
		foreach($fieldsName as $field){
			$arSelect[] = $field;
		}
		$arFilter['store_id'] = (int)$storeID;

		if(empty($from) && !empty($to)){
			$arFilter['<=date'] = new Type\DateTime($to, "Y-m-d");
		}elseif(!empty($from) && empty($to)){
			$arFilter['>=date'] =  new Type\DateTime($from, "Y-m-d");
		}elseif(!empty($from) && !empty($to)){
			$arFilter ['>=date'] = new Type\DateTime($from, "Y-m-d");
			$arFilter ['<=date'] = new Type\DateTime($to, "Y-m-d");
		}elseif(empty($from) && empty($to)){
			$db = self::getAll($fieldsName, $storeID);
			return $db;
		}
		$db = \Vogood\Statistics\StatTable::getList(array
			(
				'select'	=> $arSelect,
				'filter'	=> $arFilter
			)
		)->fetchAll();

		return $db;
	}

	/**
	 * Добавление/обновление счетчиков c проверкой на cookie
	 *
	 * @param $fieldName
	 * @param $storeID
	 * @return bool
	 */
	public static function addCounters($fieldName, $storeID){

		if(empty($fieldName) || empty($storeID))
			return false;

		$fieldsName = explode(',', $fieldName);

		$cookieName = 'VO_VISITOR_COUNTER_' . $fieldsName[0].'_'.$storeID;

		if(!isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] != 1){
			return self::addSiteVisits($fieldName, $storeID, $cookieName);
		}
	}

	/**
	 * Добавление/обновление счетчиков без проверки на cookie
	 *
	 * @param $fieldName
	 * @param $storeID
	 * @param bool|false $cookieName
	 * @param bool|false $time
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Exception
	 */
	public static function addSiteVisits($fieldName, $storeID, $cookieName = false, $time = false){

		if(empty($fieldName) && empty($storeID))
			return false;

		$cookieTime = Option::get(self::$MODULE_ID, 'cookieTime');
		$fieldsName = explode(',', $fieldName);

		if($arRow = self::getCountersList($fieldsName, $storeID)){
			$arDb = [];
			if(!empty($arRow)){
				$arRow = $arRow[0];
				foreach($arRow as $key => $field){
					if($key == 'id'){
						continue;
					}elseif($time !== false && $key == 'product_viewing_totaltime'){
						$arDb[$key] = (float)$field + $time;
					}else{
						$arDb[$key] = (int)$field + 1;
					}
				}

				$update = \Vogood\Statistics\StatTable::update($arRow['id'], $arDb);
				if ($update->isSuccess()){
					if($cookieName !== false){
						if(empty($cookieTime)){
							$cookieTime = Option::get(self::$MODULE_ID, 'cookieTime');
						}
						SetCookie($cookieName,"1",time()+($cookieTime*3600));
					}
					return true;
				}
			}
		}else{
			foreach($fieldsName as $key => $field){
				$arDb[$field] = 1;
			}
			$arDb['store_id'] = $storeID;
			$add = \Vogood\Statistics\StatTable::add($arDb);
			if ($add->isSuccess()){
				if($cookieName !== false){
					if(empty($cookieTime)){
						$cookieTime = Option::get(self::$MODULE_ID, 'cookieTime');
					}
					SetCookie($cookieName,"1",time()+($cookieTime*3600));
				}
				return true;
			}
		}
		return false;
	}
}