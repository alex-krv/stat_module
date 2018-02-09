<?
/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CAdminMenu $this
 */

use \Bitrix\Main\Localization\Loc as Loc;

$module_id = 'vogood.statistics';

if($APPLICATION->GetGroupRight($module_id) > 'D')
{
    if(\Bitrix\Main\ModuleManager::isModuleInstalled($module_id))
    {
        IncludeModuleLangFile(__FILE__);

        //сформируем верхний пункт меню
        $aMenu = array(
            'parent_menu'	=> 'global_menu_statistics',
            'sort'			=> 10,
            'text'			=> Loc::getMessage('VO_STATISTICS_STAT_NAME'),       // текст пункта меню
            'title'			=> Loc::getMessage('VO_STATISTICS_STAT_TITLE'), // текст всплывающей подсказки
            'icon'			=> 'vo_menu_icon', // малая иконка
            'page_icon'		=> 'pie-chart', // большая иконка
            'items_id'		=> 'vogood.statistics',  // идентификатор ветви
            'url'			=> 'vogood.statistics_channel_list.php?lang='.LANGUAGE_ID,
            'more_url'		=> array(
                'vogood.statistics_channel_detail.php'
            ),
            'module_id'		=> $module_id,
            'items'			=> array()
        );

        return $aMenu;
    }
}