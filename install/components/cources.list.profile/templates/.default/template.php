<? $APPLICATION->SetAdditionalCSS('https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css'); ?>
<? $APPLICATION->AddHeadScript("https://code.jquery.com/ui/1.11.4/jquery-ui.js"); ?>
<? $APPLICATION->SetAdditionalCSS('https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css'); ?>
<? 
function printTree($array)
{
    foreach ($array as $item) { ?>
    <?if(!empty($item['INFO'])) { ?>
        <div class="Content">
            <?= $item['INFO']['TITLE'] ?>
        </div>
        <div class="grid">
            <div class="grid__item">
                <p class="grid__item-title">Статус курса</p>
                <? if ($item['STATUS']['USERID'] && ($item['STATUS']['USERID'] == $item['CURRENT_USER_ID'])) { ?>
                    <p class="grid__item-info"><?= $item['STATUS']['STATUS']; ?></p>
                <? } else { ?>
                    <? foreach ($item['STATUS'] as $st) { ?>
                        <? if ($st['USERID'] === $item['CURRENT_USER_ID']) { ?>
                            <p class="grid__item-info"><?= $st['STATUS']; ?></p>
                        <? }; ?>
                    <? }; ?>
                <?};?>
            </div>
            <div class="grid__item">
                <p class="grid__item-title">Ссылка на просмотр курса</p>
                <a target="_blank" class="wr-button wr-butt-detailed grid__item-info" href="<?= $item['INFO']['VIEWURL'] ?>">Подробнее</a>
            </div>
        </div>
        <br><br>
    <? }; ?>
<? }; ?>
<? }; ?>

<?if(!empty($arResult['COURSE_INFO'])){?>
    <div id="tree" class="courses-tree" onclick="tree_toggle(arguments[0])">
        <? printTree($arResult['COURSE_INFO']); ?>
    </div>
<?}else{?>
    <span>У вас ещё нет назначенных курсов</span>
<? };?>
