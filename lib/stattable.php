<?
namespace Vogood\Statistics;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;

Loc::loadMessages(__FILE__);

class StatTable  extends Entity\DataManager{

    /**
     * @var string
     */
    private static $MODULE_ID = 'vogood.statistics';

    public static function getFilePath(){
        return __FILE__;
    }

    public static function getTableName(){
        return 'vogood_statistics';
    }

    public static function getMap(){
        return [
            new Entity\IntegerField('id', [
                'primary'       => true,
                'autocomplete'  => true,
                'title'         => Loc::getMessage('VS_ID'),
            ]),
            new Entity\IntegerField('store_id', [
                'required'  => true,
                'title'     => Loc::getMessage('VS_STORE_ID'),
            ]),
            new Entity\DatetimeField('date', [
                'default_value' => new Type\DateTime(),
                'required'      => true,
                'title'         => Loc::getMessage("VS_DATE")
            ]),
            new Entity\IntegerField("product_views_count", [
                "required"  => false,
                "title"     => Loc::getMessage("VS_PRODUCT_VIEWS_COUNT")
            ]),
            new Entity\IntegerField("product_viewing_totaltime", [
                "required"  => false,
                "title"     => Loc::getMessage("VS_PRODUCT_VIEWING_TOTALTIME")
            ]),
            new Entity\IntegerField("storeсard_visits_count", [
                "required"  => false,
                "title"     => Loc::getMessage("VS_STOREСARD_VISITS_COUNT")
            ]),
            new Entity\IntegerField("product_views_count_from_store", [
                "required"  => false,
                "title"     => Loc::getMessage("VS_PRODUCT_VIEWS_COUNT_FROM_STORE")
            ]),
            new Entity\IntegerField("product_views_count_from_catalog", [
                "required"  => false,
                "title"     => Loc::getMessage("VS_PRODUCT_VIEWS_COUNT_FROM_CATALOG")
            ]),
            new Entity\IntegerField("site_visits_count", [
                "required"  => false,
                "title"     => Loc::getMessage("VS_SITE_VISITS_COUNT")
            ]),
             new Entity\ReferenceField(
                'store',
                '\Bitrix\Iblock\Element',
                array('=this.store_id' => 'ref.ID'),
                array('join_type' => 'LEFT')
            ),

        ];
    }

}