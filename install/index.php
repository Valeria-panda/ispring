<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

global $MESS;
global $APPLICATION;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang) - strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang . "/lang/", "/install/index.php"));

class openregion_ispringintegration extends CModule
{
	var $MODULE_ID = "openregion.ispringintegration";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $PARTNER_NAME;
	var $PARTNER_URI;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path . "/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("OPENREGION_COURSE_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("OPENREGION_COURSE_MODULE_DESC");
		$this->PARTNER_NAME = Loc::getMessage("OPENREGION_COURSE_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("OPENREGION_COURSE_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/db/" . strtolower($DB->type) . "/install.sql");

		if ($this->errors !== false) {
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		} else {
			return true;
		}
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables delete
		$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/db/" . strtolower($DB->type) . "/uninstall.sql");

		if ($this->errors !== false) {
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		} else {
			return true;
		}
	}

	function InstallAgents()
	{
		if (\CAgent::AddAgent("Openregion\Ispringintegration\CSyncHelper::syncAll();", "openregion.ispringintegration", "N", 24 * 3600, "", "Y")) {
			$res = true;
		} else {
			$res = false;
		}

		if (\CAgent::AddAgent("\Openregion\Ispringintegration\CSyncHelper::syncEnrollments();", "openregion.ispringintegration", "N", 1800, "", "Y")) {
			$res = true;
		} else {
			$res = false;
		}
		return $res;
	}

	function UnInstallAgents()
	{
		\CAgent::RemoveModuleAgents("openregion.ispringintegration");
		return true;
	}

	function InstallFiles($arParams = array())
	{
		if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/components/openregioncourses"))
			mkdir($_SERVER["DOCUMENT_ROOT"] . "/local/components/openregioncourses");

		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/components",
			$_SERVER["DOCUMENT_ROOT"] . "/local/components/openregioncourses/",
			true,
			true
		);
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/public",
			$_SERVER["DOCUMENT_ROOT"] . "/extranet/courses/",
			true,
			true
		);
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/admin",
			$_SERVER["DOCUMENT_ROOT"] . "/local/admin/",
			true,
			true
		);
		return true;
	}

	function UnInstallFiles()
	{
		// Remove components
		$arComponents = scandir($_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/components");
		$key = array_search(".", $arComponents);
		unset($arComponents[$key]);
		$key = array_search("..", $arComponents);
		unset($arComponents[$key]);
		foreach ($arComponents as $c) {
			DeleteDirFilesEx("/local/components/openregioncourses/" . $c);
		}

		// Remove admin files
		$arComponents = scandir($_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/admin");
		$key = array_search(".", $arComponents);
		unset($arComponents[$key]);
		$key = array_search("..", $arComponents);
		unset($arComponents[$key]);
		foreach ($arFiles as $f) {
			unlink($_SERVER["DOCUMENT_ROOT"] . "/local/admin/" . $f);
		}

		return true;
	}


	function AddIblockType()
	{
		global $DB;
		Loader::IncludeModule('iblock');

		// код для типа инфоблоков
		$iblockTypeCode = 'ispring';

		// проверяем на уникальность
		$db_iblock_type = \CIBlockType::GetList(
			['SORT' => 'ASC'],
			['ID' => $iblockTypeCode]
		);
		// если его нет - создаём
		if (!$ar_iblock_type = $db_iblock_type->Fetch()) {
			$obBlocktype = new \CIBlockType;
			$DB->StartTransaction();

			// массив полей для нового типа инфоблоков
			$arIBType = [
				'ID' => $iblockTypeCode,
				'SECTIONS' => 'Y',
				'IN_RSS' => 'N',
				'SORT' => 500,
				'LANG' =>
				[
					'en' =>
					[
						'NAME' => 'Ispring',
					],
					'ru' =>
					[
						'NAME' => 'Ispring',
					]
				]
			];

			// создаём новый тип для инфоблоков
			$resIBT = $obBlocktype->Add($arIBType);
			if (!$resIBT) {
				$DB->Rollback();
				echo 'Error: ' . $obBlocktype->LAST_ERROR;
				die();
			} else {
				$DB->Commit();
			}
		} else {
			return false;
		}

		return true;
	}


	// функция для создания инфоблока
	function AddIblock()
	{
		Loader::IncludeModule('iblock');

		$iblockCode = 'enrollments'; // символьный код для инфоблока
		$iblockType = 'ispring'; // код типа инфоблоков

		$ib = new \CIBlock;

		// проверка на уникальность
		$resIBlock = \CIBlock::GetList(
			[],
			[
				'TYPE' => $iblockType,
				'CODE' => $iblockCode
			]
		);
		if ($arIBlock = $resIBlock->Fetch()) {
			return false;
		} else {
			$arFieldsIB =
				[
					'ACTIVE' => 'Y',
					'NAME' => 'Enrollments',
					'CODE' => $iblockCode,
					'IBLOCK_TYPE_ID' => $iblockType,
					'SITE_ID' => 's1',
					'GROUP_ID' => ['2' => 'R'],
					'FIELDS' =>
					[
						'CODE' =>
						[
							'IS_REQUIRED' => 'Y',
							'DEFAULT_VALUE' =>
							[
								'TRANS_CASE' => 'L',
								'UNIQUE' => 'Y',
								'TRANSLITERATION' => 'Y',
								'TRANS_SPACE' => '-',
								'TRANS_OTHER' => '-'
							]
						]
					]
				];
			return $ib->Add($arFieldsIB);
		}
	}


	function AddProp($IBLOCK_ID)
	{
		Loader::IncludeModule('iblock');
		// массив полей для нового свойства
		$arProperty =
			[
				[
					'NAME' => 'ID назначения',
					'ACTIVE' => 'Y',
					'SORT' => '1',
					'CODE' => 'ENROLLMENTID',
					'IS_REQUIRED' => 'Y',
					'PROPERTY_TYPE' => 'S',
					'IBLOCK_ID' => $IBLOCK_ID
				],
				[
					'NAME' => 'ID пользователя',
					'ACTIVE' => 'Y',
					'SORT' => '2',
					'CODE' => 'UID',
					'IS_REQUIRED' => 'Y',
					'PROPERTY_TYPE' => 'S',
					'IBLOCK_ID' => $IBLOCK_ID
				],
				[
					'NAME' => 'Дата начала',
					'ACTIVE' => 'Y',
					'SORT' => '1',
					'CODE' => 'ACCESSDATE',
					'IS_REQUIRED' => 'Y',
					'PROPERTY_TYPE' => 'S',
					'IBLOCK_ID' => $IBLOCK_ID
				],
				[
					'NAME' => 'Дата окончания',
					'ACTIVE' => 'Y',
					'SORT' => '1',
					'CODE' => 'DUEDATE',
					'IS_REQUIRED' => 'Y',
					'PROPERTY_TYPE' => 'S',
					'IBLOCK_ID' => $IBLOCK_ID
				]
			];

		foreach ($arProperty as $property) {
			$arFieldsProp = $property;
			$ibp = new \CIBlockProperty;
			// создаём свойство
			$propID[] = $ibp->Add($arFieldsProp);
		}
		return $propID;
	}


	// удаление данных инфоблоков
	function DelIblocks()
	{
		global $DB;
		Loader::IncludeModule("iblock");

		$DB->StartTransaction();
		if (!\CIBlockType::Delete('ispring')) {
			$DB->Rollback();

			\CAdminMessage::ShowMessage(array(
				"TYPE" => "ERROR",
				"MESSAGE" => GetMessage("VTEST_IBLOCK_TYPE_DELETE_ERROR"),
				"DETAILS" => "",
				"HTML" => true
			));
		}
		$DB->Commit();
	}


	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->InstallDB();
		$this->InstallFiles();
		$this->InstallAgents();
		$this->AddIblockType();
		$IBLOCK_ID = $this->AddIblock();
		$this->AddProp($IBLOCK_ID);
		RegisterModule($this->MODULE_ID);
		$APPLICATION->IncludeAdminFile(Loc::getMessage("OPENREGION_COURSE_INSTALL_TITLE"), $DOCUMENT_ROOT . "/local/modules/" . $this->MODULE_ID . "/install/step.php");
	}

	function DoUninstall()
	{
		global  $DOCUMENT_ROOT, $APPLICATION;
		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallAgents();
		$this->DelIblocks();
		UnRegisterModule($this->MODULE_ID);
		$APPLICATION->IncludeAdminFile(Loc::getMessage("OPENREGION_COURSE_UNINSTALL_TITLE"), $DOCUMENT_ROOT . "/local/modules/" . $this->MODULE_ID . "/install/unstep.php");
	}
}
