<?
namespace Vogood\Statistics;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;

class PageInfo {
    private static $MODULE_ID = 'vogood.statistics';
    private static $regExpStoreCard;
    private static $regExpProductCard;
    private static $regExpCatalog;

    protected static $instance;

    protected function __construct() {}

    private function __clone() {}

    private function __wakeup() {}

    public static function getInstance()
    {
        if (self::$instance === null)
        {
            self::$instance = new self(func_get_args());
        }
        return self::$instance;
    }

    /**
     * Определяет предыдущую страницу
     *
     * @return bool|string
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function getPrevPage(){
        if(!empty($_SERVER['HTTP_REFERER'])){

            $previousUri = explode("?", $_SERVER['HTTP_REFERER']);
            $previousUri = explode("://", $previousUri[0]);
            $previousUri = explode(SITE_SERVER_NAME, $previousUri[1]);

            if(empty(self::$regExpCatalog)){
                self::$regExpCatalog = Option::get(self::$MODULE_ID, 'regExpCatalog');
            }
            if(empty(self::$regExpStoreCard)){
                self::$regExpStoreCard = Option::get(self::$MODULE_ID, 'regExpStoreCard');
            }
            if(empty(self::$regExpProductCard)){
                self::$regExpProductCard = Option::get(self::$MODULE_ID, 'regExpProductCard');
            }
            if(preg_match(self::$regExpProductCard, $previousUri[1])){
                $fieldName = 'productCard';
                return $fieldName;
            }
            if(preg_match(self::$regExpCatalog, $previousUri[1])){
                $prevPageName = 'catalog';
                return $prevPageName;
            }
            if(preg_match(self::$regExpStoreCard, $previousUri[1])){
                $prevPageName = 'storeCard';
                return $prevPageName;
            }

        }
        return false;
    }

    /**
     * Определяет текущую страницу
     *
     * @return array|bool
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function getCurPage(){
        $request = Application::getInstance()->getContext()->getRequest();
        $uriString = $request->getRequestUri();
        $uri = new Uri($uriString);
        $uri = $uri->getUri();
        $curPageInfo['URL'] = $uri;
        if(empty(self::$regExpStoreCard)){
            self::$regExpStoreCard = Option::get(self::$MODULE_ID, 'regExpStoreCard');
        }
        if(empty(self::$regExpProductCard)){
            self::$regExpProductCard = Option::get(self::$MODULE_ID, 'regExpProductCard');
        }
        if(preg_match(self::$regExpStoreCard, $uri)){
            $curPageInfo['NAME'] = 'storeCard';
            return $curPageInfo;
        }elseif(preg_match(self::$regExpProductCard, $uri)){
            $curPageInfo['NAME'] = 'productCard';

            if($prevPage = self::getPrevPage()){
                $curPageInfo['PREV_PAGE'] =  $prevPage;
                return $curPageInfo;
            }
            return $curPageInfo;
        }
        return false;
    }

}
