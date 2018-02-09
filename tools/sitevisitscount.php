<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Vogood\Statistics\General;

if(Loader::includeModule("vogood.statistics")){
    $request = Application::getInstance()->getContext()->getRequest();

    $storeId = htmlspecialchars(trim($request->get("storeId")));

    $fieldName = 'site_visits_count';
    if(!empty($storeId)){
    	if(General::addSiteVisits($fieldName, $storeId)){
	        echo json_encode(true);
	    }else{
	    	echo json_encode('Не удалось произвести запись в БД');
	    }
    }else{
    	echo json_encode('Невозможно получить ID магазина');
    }
}

