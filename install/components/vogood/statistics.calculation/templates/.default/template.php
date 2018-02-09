<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>
<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__); ?>

<div class="b-store-statistics">
	<div class="store-statistics__period">
		<div class="period-title"><?=Loc::getMessage("SC_STORE_STATISTICS")?></div>
		<form  class="stat-period">
			<div class="period-limit">
				<div class="period-limit-from-wrapper">
					<input type="date" name="limitFrom" class="period-limit-from" value="" min="<?=$arParams['BEGIN_DATE']?>" max="<?=date('Y-m-d')?>">
				</div>
				<div class="period-limit-to-wrapper">
					<input type="date" name="limitTo" class="period-limit-to" value="" min="<?=$arParams['BEGIN_DATE']?>" max="<?=date('Y-m-d')?>">
				</div>
			</div>
			<div class="period-btn-wrapper">
				<input type="submit" name="OK" class="period-btn-send" value="<?=Loc::getMessage("BEGIN_BTN_SHOW")?>">
			</div>
		</form>
	</div>
	<div class="error-message _hide"><?=Loc::getMessage("SC_ERROR")?></div>
	<div class="store-statistics__values">
		<?if($arParams['DISPLAY_STORE_VISITS'] == 'Y'):?>
			<div class="values-item">
				<div class="values-item-name"><?=Loc::getMessage("SC_STORE_VISITS")?></div>
				<div class="values-item-index _store-visits"><?=$arResult['COUNTER_VALUES'][0]['storeсard_visits_count_sum']?></div>
			</div>
		<?endif;?>
		<?if($arParams['DISPLAY_PRODUCT_VIEWS'] == 'Y'):?>
			<div class="values-item">
				<div class="values-item-name"><?=Loc::getMessage("SC_VIEWING_GOODS")?></div>
				<div class="values-item-index _product-views"><?=$arResult['COUNTER_VALUES'][0]['product_views_count_sum']?></div>
			</div>
		<?endif;?>
		<?if($arParams['DISPLAY_SITE_TRANSITIONS'] == 'Y'):?>
			<div class="values-item">
				<div class="values-item-name"><?=Loc::getMessage("SC_SITE_VISITS")?></div>
				<div class="values-item-index _site-visits"><?=$arResult['COUNTER_VALUES'][0]['site_visits_count_sum']?></div>
			</div>
		<?endif;?>
	</div>
</div>
