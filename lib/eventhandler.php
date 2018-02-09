<?
namespace Vogood\Statistics;

use \Bitrix\Main\Type;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use \Bitrix\Main\Context;


class EventHandler {

	private static $MODULE_ID = 'vogood.statistics';
	private static $bWasHere = false;

	/**
	 * Обновляет количество посетителей для карточки товара и карточки магазина
	 *
	 * @return array|bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function updateVisitorCounter(){
		$cookieTime = Option::get(self::$MODULE_ID, 'forgetTime');
		$catalogIblockId = Option::get(self::$MODULE_ID, 'catalogIblockId');

		\CJSCore::Init(array('jquery2'));
		\CUtil::InitJSCore(array('custom_main'));

		if(Context::getCurrent()->getRequest()->isAdminSection() || Context::getCurrent()->getRequest()->isAjaxRequest() || self::$bWasHere)
			return true;
		
		if($prev = PageInfo::getInstance()->getPrevPage()){
			if($prev == 'productCard'){
				$fieldName = 'product_viewing_totaltime';
				$arCurStores = unserialize($_COOKIE['VO_CUR_STORES']);
				foreach($_COOKIE as $key => $value){
					if(strpos($key, 'VO_TIME_COUNTER_') !== false){
						if($storeID = explode('_', $key)){
							$storeID = $storeID['3'];
							$time = (date("U") - strtotime($value))/60;
							if(General::addSiteVisits($fieldName, $storeID, false, $time)){
								if(in_array($key, $arCurStores)){
									continue;
								}
								if(empty($cookieTime))
									$cookieTime = Option::get(self::$MODULE_ID, 'forgetTime');
								SetCookie($key,$value,time()-($cookieTime*60),'/');
							}
						}
					}
				}
			}
		}

		if($curPageInfo = PageInfo::getInstance()->getCurPage()){
			if($curPageInfo['NAME'] == 'storeCard'){
				$storeID = explode('/', $curPageInfo['URL']);
				$storeID = $storeID[3];

				General::addCounters('storeсard_visits_count', $storeID);

			}elseif($curPageInfo['NAME'] == 'productCard'){
				preg_match('/\/*[0-9]+\//', $curPageInfo['URL'], $productID);
				$productID = str_replace('/', '', $productID[0]);

				if(Loader::includeModule("iblock")){
					$arSelect = Array("ID", "SECTION_ID", "PROPERTY_ATTACH_SHOPS");
					if(empty($catalogIblockId))
						$catalogIblockId = Option::get(self::$MODULE_ID, 'catalogIblockId');
					$arFilter = Array("IBLOCK_ID"=>$catalogIblockId, 'ID' => $productID);
					$res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
					$arCurStore = array();
					while($ob = $res->GetNextElement())
					{
						$arFields = $ob->GetFields();
						$storeID = $arFields['PROPERTY_ATTACH_SHOPS_VALUE'];
						$fieldName = 'product_views_count';

						$cookieName = 'VO_TIME_COUNTER_'.$storeID;
						$arCurStore[] = $cookieName;
						if(empty($cookieTime))
							$cookieTime = Option::get(self::$MODULE_ID, 'forgetTime');
						SetCookie($cookieName,date("Y-m-d H:i:s"),time()+($cookieTime*60), '/');

						General::addCounters($fieldName, $storeID);

						if(!empty($curPageInfo['PREV_PAGE'])){
							if($curPageInfo['PREV_PAGE'] == 'catalog' || $curPageInfo['PREV_PAGE'] == 'productCard'){
								$fieldName = 'product_views_count_from_catalog';
								General::addCounters($fieldName, $storeID);
							}elseif($curPageInfo['PREV_PAGE'] == 'storeCard'){
								$fieldName = 'product_views_count_from_store';
								General::addCounters($fieldName, $storeID);
							}
						}
					}
					if(!empty($arCurStore)){
						if(empty($cookieTime))
							$cookieTime = Option::get(self::$MODULE_ID, 'forgetTime');
						SetCookie('VO_CUR_STORES', serialize($arCurStore), time()+($cookieTime*60), '/');
					}
				}
			}
		}
	}
}
