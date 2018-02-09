<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config as Conf;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\Application;
use Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

Class vogood_statistics extends CModule{

	var $exclusionAdminFiles;

	public function __construct(){
		$arModuleVersion = array();
		include(__DIR__."/version.php");

		$this->exclusionAdminFiles=array(
			'..',
			'.',
			'menu.php',
		);

		$this->MODULE_ID = 'vogood.statistics';
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("VOGOOD_STATISTICS_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("VOGOOD_STATISTICS_MODULE_DESC");

		$this->PARTNER_NAME = Loc::getMessage("VOGOOD_STATISTICS_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("VOGOOD_STATISTICS_PARTNER_URI");

		$this->MODULE_SORT = 1;
		$this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
		$this->MODULE_GROUP_RIGHTS = 'Y';
	}

	/**
	 * Определяем место размещения модуля
	 *
	 * @param bool|true $useDocumentRoot
	 * @return mixed|string
	 */
	public function GetPath($useDocumentRoot = true){
		$filePath = dirname(__DIR__);
		$filePath = str_replace('\\', '/', $filePath);
		if($useDocumentRoot)
			return $filePath;
		else
			return str_replace(Application::getDocumentRoot(), '', $filePath);
	}

	/**
	 * Определяем что система поддерживает D7
	 *
	 * @return bool
	 */
	public function isVersionD7(){
		return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
	}

	/**
	 * Установка БД
	 *
	 * @return bool
	 */
	public function InstallDB(){
		global $DB, $APPLICATION;

        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/local/modules/" . $this->MODULE_ID . "/install/db/mysql/install.sql");

        // если при выполнении запроса были ошибки, выдаем их в сообщении
        if($this->errors !== false)
        {
            $APPLICATION->ThrowException(implode("<br>", $this->errors));
            return false;
        }
        return true;

	}

	/**
	 * Удаление БД
	 *
	 * @return array|bool
	 */
	public function UnInstallDB(){
		global $DB;
        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/local/modules/" . $this->MODULE_ID . "/install/db/mysql/uninstall.sql");
        if (!$this->errors)
            return true;
        else
            return $this->errors;
	}

	/**
	 * Установка файлов
	 *
	 * @return bool
	 */
	public function InstallFiles(){
		CopyDirFiles($this->GetPath().'/install/components', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components', true, true);
		CopyDirFiles($this->GetPath().'/install/js', $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/', true, true);
		CopyDirFiles($this->GetPath().'/install/tools', $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/', true, true);
		CopyDirFiles($this->GetPath().'/install/themes', $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true);
		

		if(\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/admin')) {
			if ($dir = opendir($path)) {
				while (false !== $item = readdir($dir)) {
					if (in_array($item, $this->exclusionAdminFiles))
						continue;
					file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item, '<' . '? require($_SERVER["DOCUMENT_ROOT"]."' . $this->GetPath(false) . '/admin/' . $item . '");?' . '>');
				}
				closedir($dir);
			}
		}

		if(\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/install/components/')) {
			if ($dir = opendir($path)) {
				while (false !== $item = readdir($dir)) {
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($path.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/local/components/'.$item, true, true);
				}
				closedir($dir);
			}
		}

		return true;
	}

	/**
	 * Удаление файлов
	 *
	 * @return bool
	 */
	public function UnInstallFiles(){
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/'.$this->MODULE_ID.'/');
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/js/'.$this->MODULE_ID.'/');
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/tools/'.$this->MODULE_ID.'/');
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/themes/.default/icons/' . $this->MODULE_ID.'/');
		
		DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"] .'/bitrix/themes/.default/vogood.statistics.css');
		

		if(\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/admin')){
			if($dir = opendir($path)){
				while (false !== $item = readdir($dir)) {
					if(in_array($item, $this->exclusionAdminFiles))
						continue;
					\Bitrix\Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$this->MODULE_ID.'_'.$item);
				}
				closedir($dir);
			}
		}

		if(\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/install/components/')){
			if($dir = opendir($path)){
				while (false !== $item = readdir($dir)) {
					if ($item == '..' || $item == '.' || !is_dir($path0 = $path.'/'.$item))
						continue;
					\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/local/components/'.$item);
				}
				closedir($dir);
			}
		}

		return true;
	}

	/**
	 * Установка событий
	 *
	 * @return bool
	 */
	public function InstallEvents()
	{
		EventManager::getInstance()->registerEventHandler(
            'main',
            'OnPageStart',
            $this->MODULE_ID,
            'Vogood\Statistics\EventHandler',
            'updateVisitorCounter'
        );
		return true;
	}

	/**
	 * Удаление событий
	 *
	 * @return bool
	 */
	public function UnInstallEvents()
	{
		EventManager::getInstance()->unRegisterEventHandler(
            'main',
            'OnPageStart',
            $this->MODULE_ID,
            'Vogood\Statistics\EventHandler',
            'updateVisitorCounter'
        );
		return true;
	}

	/**
	 * Установка модуля
	 */
	public function DoInstall(){
		global $APPLICATION;
		if($this->isVersionD7()){
			$this->InstallDB();
			$this->InstallEvents();
			$this->InstallFiles();

			\Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
		}else{
			$APPLICATION->ThrowException(Loc::getMessage('VOGOOD_STATISTICS_ERROR_VERSION'));
		}
		$APPLICATION->IncludeAdminFile(Loc::getMessage('VOGOOD_STATISTICS_INSTALL_TITLE'), $this->GetPath().'/install/step.php');
	}

	/**
	 * Удаление модуля
	 */
	public function DoUninstall(){
		global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

		if($request['step'] < 2){
			$APPLICATION->IncludeAdminFile(Loc::getMessage('VOGOOD_STATISTICS_UNINSTALL_TITLE'), $this->GetPath().'/install/unstep1.php');
		}elseif($request['step'] == 2){
			$this->UnInstallFiles(); 
			$this->UnInstallEvents();
			if($request["savedata"] != 'Y')
				$this->UnInstallDB();

			\Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
			$APPLICATION->IncludeAdminFile(Loc::getMessage('VOGOOD_STATISTICS_UNINSTALL_TITLE'), $this->getPath().'/install/unstep2.php');
		}
	}

	/**
	 * Устанавливаем доступы для модуля
	 *
	 * @return array
	 */
	public function GetModuleRightList(){
		$arr = array(
			'reference_id' => array('D','R','W'),
			'reference' => array(
				Loc::getMessage('MOD_ACCESS_DENIED'),
				Loc::getMessage('MOD_ACCESS_OPENED'),
				Loc::getMessage('MOD_ACCESS_FULL')
			)
		);
		return $arr;
	}

}
