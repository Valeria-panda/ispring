<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

$module_id = "openregion.ispringintegration";
if (!CModule::IncludeModule($module_id)) {
    die();
}


if (!$USER->IsAdmin()) return;

$aTabs = [
    [
        "DIV" => "parameters",
        "TAB" => Loc::getMessage("TAB_TITLE_NAME_SHORT"),
        "TITLE" => Loc::getMessage("OPTIONS_TAB_COMMON"),
        "OPTIONS" => [
            Loc::getMessage("OPTIONS_TAB_COMMON"),
            [
                "SECRET_KEY",
                Loc::getMessage("SECRET_KEY"),
                Option::get($module_id, strtolower("SECRET_KEY"), Loc::getMessage("SECRET_KEY")),
                ["text"]
            ],
            [
                "PASSWORD",
                Loc::getMessage("PASSWORD"),
                Option::get($module_id, strtolower("PASSWORD"), Loc::getMessage("PASSWORD")),
                ["text"]
            ],
            [
                "EMAIL",
                Loc::getMessage("EMAIL"),
                Option::get($module_id, strtolower("EMAIL"), Loc::getMessage("EMAIL")),
                ["text"]
            ],
        ]
    ],
];
?>

<form method="post" action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($module_id) ?>&amp;lang=<?= LANGUAGE_ID ?>">
    <? $tabControl = new CAdminTabControl("tabControl", $aTabs); ?>
    <? $tabControl->Begin();
    foreach ($aTabs as $aTab) {
        if ($aTab["OPTIONS"]) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
        }
    }

    $tabControl->buttons();
    ?>
    <? $tabControl->End(); ?>

    <input type="submit" name="save" id="save" value="<?= Loc::getMessage('APPLY_BUTTON_TITLE'); ?>" class="adm-btn-save" />

</form>

<?
if ($_SERVER['REQUEST_METHOD']    == 'POST' &&    $_REQUEST['save']    != "") {
    foreach ($aTabs    as    $aTab) {
        __AdmSettingsSaveOptions($module_id,    $aTab['OPTIONS']);
    }
    LocalRedirect($APPLICATION->GetCurPage()    . '?lang=' . LANGUAGE_ID .
        '&mid_menu=1&mid=' . urlencode($module_id)    . '&tabControl_active_tab=' .
        urlencode($_REQUEST['tabControl_active_tab'])    . '&sid=' .
        urlencode($siteId));
}
?>