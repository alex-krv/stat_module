<?php

// Автозагрузка классов
CModule::AddAutoloadClasses('vogood.statistics', array(
	'Vogood\Statistics\StatTable'	=> 'lib/stattable.php',
	'Vogood\Statistics\General'			=> 'lib/general.php',
	'Vogood\Statistics\EventHandler'	=> 'lib/eventhandler.php',
	'Vogood\Statistics\PageInfo'	=> 'lib/pageinfo.php',
	'Vogood\Statistics\SiteVisitsCount'	=> 'tools/sitevisitscount.php',
));

//Регистрация js
$arJsConfig = array(
	'custom_main' => array(
		'js'	=> '/bitrix/js/vogood.statistics/script.js',
	)
);
foreach ($arJsConfig as $ext => $arExt) {
	\CJSCore::RegisterExt($ext, $arExt);
}

