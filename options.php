<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');?>
<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');?>
<?
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

global $APPLICATION;
$module_id = 'vogood.statistics';

\Bitrix\Main\Loader::includeModule($module_id);

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
Loc::loadMessages(__FILE__);


$APPLICATION->SetTitle(Loc::getMessage('VO_STAT_COOKIE_TITLE'));

$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);

$arEventMessage = array();
$rsET = CEventType::GetList(array('LID' => SITE_ID), array('EVENT_NAME' => 'ASC'));
while($arEventType = $rsET->Fetch())
{
    $arEventMessage[$arEventType['EVENT_NAME']] = array(
        'LABEL'     => $arEventType['NAME'] . ' [' . $arEventType['EVENT_NAME'] . ']',
        'OPTIONS'   => array()
    );
}
$arIblocks = [];
if(Loader::includeModule("iblock")){
    $rsEM = \CIBlock::GetList(
        Array(), 
        Array(
            'TYPE'=>'aspro_mshop_content', 
            'SITE_ID'=>'s1', 
            'ACTIVE'=>'Y'
        ), true
    );

    while($arEvent = $rsEM->Fetch()){
        $arIblocks[$arEvent['ID']] = $arEvent['NAME'];
       
    }
}
$arCatalog = [];
if(Loader::includeModule("iblock")){
    $rsEM = \CIBlock::GetList(
        Array(), 
        Array(
            'TYPE'=>'aspro_mshop_catalog', 
            'SITE_ID'=>'s1', 
            'ACTIVE'=>'Y'
        ), true
    );

    while($arEvent = $rsEM->Fetch()){
        $arCatalog[$arEvent['ID']] = $arEvent['NAME'];
       
    }
}

$arAllOptions = array(
    'TAB1'	=> array(
        'TITLE'		=> Loc::getMessage('VO_STAT_PARAMS'),
        'GROUPS'	=> array(
            array(
                'TITLE'		=> Loc::getMessage('VO_STAT_TIME_PARAMS'),
                'OPTIONS'	=> array(
                    array('cookieTime', Loc::getMessage('VO_STAT_COOKIE_TIME'), '24', array('text', 3)),
                    array('forgetTime', Loc::getMessage('VO_STAT_FORGET_TIME'), '30', array('text', 3)),
                ),
            ),
            array(
                'TITLE'		=> Loc::getMessage('VO_STAT_REGULAR_EXP'),
                'OPTIONS'	=> array(
                    array('regExpStoreCard', Loc::getMessage('VO_STAT_REG_EXP_STORE'), '/^\/contacts\/stores\/[0-9]+\/\?*.*$/', array('text', 90)),
                    array('regExpProductCard', Loc::getMessage('VO_STAT_REG_EXP_PRODUCT'), '/^\/catalog\/*.*\/*[0-9]+\/\?*.*$/', array('text', 90)),
                    array('regExpCatalog', Loc::getMessage('VO_STAT_REG_EXP_CATALOG'), '/^\/catalog\/.*$/', array('text', 90)),
                ),
            ),
            array(
                'TITLE'     => Loc::getMessage('VO_STAT_OTHER'),
                'OPTIONS'   => array(
                    array('storeIblockId', Loc::getMessage('VO_STAT_STORE_IBLOCK_ID'), '', array('select', $arIblocks)),
                    array('catalogIblockId', Loc::getMessage('VO_STAT_CATALOG_IBLOCK_ID'), '', array('select', $arCatalog)),
                    array('brandIblockId', Loc::getMessage('VO_STAT_BRAND_IBLOCK_ID'), '', array('select', $arIblocks)),
                    
                ),
            ),
        )
    ),
);
$aTabs = array();
foreach($arAllOptions as $tabCode=>$arTab){
    $aTabs[] = array('DIV' => 'edit_'.strtolower($tabCode), 'TAB' => $arTab['TITLE'], 'ICON' => '', 'TITLE' => '');
}
$tabControl = new CAdminTabControl('tabControl', $aTabs);

//Process form
if($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid()){

    foreach($arAllOptions as $arTab){

        foreach($arTab['GROUPS'] as $arGroup)
        {
            foreach($arGroup['OPTIONS'] as $arOption){
                $fieldName = $arOption[0];
                $name = $arOption[0];
                $defaultValue = $arOption[2];
                $type = $arOption[3][0];

                if($arOption[3][1] == 'disabled' || $arOption[4] == 'readonly'){
                    continue;
                }

                $value = $_REQUEST[$fieldName];

                if($type == 'checkbox'){
                    $value = ($value == 'Y' ? 'Y' : 'N');
                }
                if($value){
                    if($type == 'file'){
                        $oldFileID = Option::get($module_id, $name, $defaultValue);
                        $arFile = array();

                        if($_REQUEST[$fieldName.'_del'] == 'Y'){
                            CFile::Delete($oldFileID);
                            $value = '';
                        }else{
                            if(!empty($_FILES[$fieldName]['name'])){
                                $arFile = $_FILES[$fieldName];
                            }elseif(!empty($_REQUEST[$fieldName])){
                                $filePath = $_REQUEST[$fieldName];
                                $arFile = CFile::MakeFileArray($filePath);
                            }

                            $fileDescription = $_REQUEST[$fieldName.'_descr'];

                            $arAddFile = array(
                                'old_file'		=> $oldFileID,
                                'del'			=> ($arFile['name'] != '' ? 'Y' : ''),
                                'MODULE_ID'		=> $module_id,
                                'description'	=> $fileDescription,
                            );

                            $newFileID = CFile::SaveFile(array_merge($arFile, $arAddFile), '/');
                            $value = ($newFileID > 0 ? $newFileID : $oldFileID);
                        }
                    }else{
                        if($type == 'combobox' || $type == 'text-list'){
                            if($type == 'text-list'){
                                $value = array_filter($value, function($v, $k){
                                    $v = trim($v);
                                    return !empty($v);
                                });
                            }
                             
                            $value = serialize($value);
                        }
                    }
                }
                Option::set($module_id, $name, $value);
            }
        }

    }

}
?>

    <form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>" name="vogood_stat_settings" enctype="multipart/form-data">
        <?=bitrix_sessid_post()?>
        <?
        $tabControl = new CAdminTabControl('tabControl', $aTabs);
        $tabControl->Begin();
        ?>

        <?foreach($arAllOptions as $arTab):?>

            <?$tabControl->BeginNextTab();?>

            <?foreach($arTab['GROUPS'] as $arGroup):?>
                <?if($arGroup['TITLE']):?>
                    <tr class="heading"><td colspan="2"><b><?=$arGroup['TITLE']?></b></td></tr>
                <?endif;?>
                <?foreach($arGroup['OPTIONS'] as $arOption):
                    $fieldName = $arOption[0];
                    $type = $arOption[3];
                    $bReadonly = ($arOption[4] === 'readonly');
                    $val = Option::get($module_id, $arOption[0], $arOption[2]);
                    $notice = $arOption[5];
                    ?>
                    <?if($type[0] == 'hidden'):?>
                    <input id="<?echo htmlspecialcharsbx($fieldName)?>" type="hidden" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($fieldName)?>">
                <?else:?>
                    <tr>
                        <td width="40%" nowrap <?if($type[0]=='textarea') echo 'class="adm-detail-valign-top"'?>>
                            <label for="<?echo htmlspecialcharsbx($fieldName)?>"><?echo $arOption[1]?>:</label>
                        <td width="60%">
                            <?if($bReadonly):?>
                                <?=$val?>
                            <?elseif($type[0] == 'checkbox'):?>
                                <input type="checkbox" id="<?echo htmlspecialcharsbx($fieldName)?>" name="<?echo htmlspecialcharsbx($fieldName)?>" value="Y"<?if($val=='Y')echo' checked';?><?if($type[1] == 'disabled'):?> disabled="true"<?endif;?>>
                            <?elseif($type[0] == 'text'):?>
                                <input id="<?echo htmlspecialcharsbx($fieldName)?>" type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($fieldName)?>">
                            <?elseif($type[0] == 'password'):?>
                                <input id="<?echo htmlspecialcharsbx($fieldName)?>" type="password" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($fieldName)?>">
                            <?elseif($type[0] == 'textarea'):?>
                                <textarea id="<?echo htmlspecialcharsbx($fieldName)?>" rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($fieldName)?>"><?echo htmlspecialcharsbx($val)?></textarea>
                            <?elseif($type[0] == 'select'):?>
                                <select name="<?echo htmlspecialcharsbx($fieldName)?>">
                                    <?foreach($type[1] as $value=>$name):?>
                                        <option value="<?=$value?>"<?if($value == $val):?> selected<?endif;?>><?=$name?></option>
                                    <?endforeach;?>
                                </select>
                            <?elseif($type[0] == 'combobox'):
                                $val = unserialize($val);
                                if(!is_array($val)){
                                    $val = array();
                                }
                                ?>
                                <select name="<?echo htmlspecialcharsbx($fieldName)?>[]" multiple size="<?=$type[2]?>">
                                    <?if($type[3] == 'grouped'):?>
                                        <?foreach($type[1] as $arGroup):?>
                                            <?if(!empty($arGroup['OPTIONS'])):?>
                                                <optgroup label='<?=$arGroup['LABEL']?>'>
                                                    <?foreach($arGroup['OPTIONS'] as $value=>$name):?>
                                                        <option value="<?=$value?>"<?if(in_array($value, $val)):?> selected<?endif;?>><?=$name?></option>
                                                    <?endforeach;?>
                                                </optgroup>
                                            <?endif;?>
                                        <?endforeach;?>
                                    <?else:?>
                                        <?foreach($type[1] as $value=>$name):?>
                                            <option value="<?=$value?>"<?if(in_array($value, $val)):?> selected<?endif;?>><?=$name?></option>
                                        <?endforeach;?>
                                    <?endif;?>
                                </select>
                            <?elseif($type[0] == 'text-list'):
                                $aVal = unserialize($val);
                                $aValCount = count($aVal);
                                for($j = 0; $j < $aValCount; $j++):
                                    ?>
                                    <input type="text" size="<?echo $type[2]?>" value="<?echo htmlspecialcharsbx($aVal[$j])?>" name="<?echo htmlspecialcharsbx($fieldName)."[]"?>"><br><?
                                endfor;
                                for($j = 0; $j < $type[1]; $j++):
                                    ?><input type="text" size="<?echo $type[2]?>" value="" name="<?echo htmlspecialcharsbx($fieldName)."[]"?>"><br><?
                                endfor;?>
                            <?elseif($type[0] == 'file'):?>

                            <?endif?>
                        </td>
                    </tr>
                    <?if(!empty($notice)):?>
                        <tr>
                            <td width="40%" nowrap></td>
                            <td width="60%">
                                <?echo BeginNote();?>
                                <?=$notice?>
                                <?echo EndNote();?>
                            </td>
                        </tr>
                    <?endif;?>
                <?endif?>
                <?endforeach;?>
            <?endforeach;?>

            <?$tabControl->EndTab();?>

        <?endforeach;?>

        <?$tabControl->Buttons();?>
        <input type="submit" name="Save" value="<?=GetMessage('MAIN_SAVE')?>" title="<?=GetMessage('MAIN_OPT_SAVE_TITLE')?>" class="adm-btn-save">
        <?$tabControl->End();?>
    </form>

<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');?>