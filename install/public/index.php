<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Курсы Ispring");

global $USER;
if ($USER->IsAdmin()) {
    $APPLICATION->IncludeComponent(
        "openregioncourses:cources.list",
        ".default",
        array(
            "COMPOSITE_FRAME_MODE" => "A",
            "COMPOSITE_FRAME_TYPE" => "AUTO",
            "DATE_FORMAT" => "Y-m-d",
            "COMPONENT_TEMPLATE" => ".default",
        ),
        false
    );
} else { ?>
    <span>Вам нужно обладать правами администратора, для просмотра списка всех курсов</span>
<? } ?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>