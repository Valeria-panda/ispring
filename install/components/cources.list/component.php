<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?
$module_id = "openregion.ispringintegration";
if (!CModule::IncludeModule($module_id)) {
    echo "Модуль курсов не установлен";
    return;
}
use \Openregion\Ispringintegration\CApiHelper;

$courseList = CApiHelper::getCoursesTree();

$arResult['COURSES'] = $courseList;

$this->IncludeComponentTemplate();
