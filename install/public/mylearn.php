<?
global $USER;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Мои курсы"); ?>

<? $APPLICATION->IncludeComponent(
    "openregioncourses:cources.list.student",
    ".default",
    array(
        "COMPOSITE_FRAME_MODE" => "A",
        "COMPOSITE_FRAME_TYPE" => "AUTO",
        "DATE_FORMAT" => "Y-m-d",
        "COMPONENT_TEMPLATE" => ".default",
    ),
    false
); ?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>