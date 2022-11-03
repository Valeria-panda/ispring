<? $APPLICATION->SetAdditionalCSS('https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css'); ?>
<? $APPLICATION->AddHeadScript("https://code.jquery.com/ui/1.11.4/jquery-ui.js"); ?>
<? $APPLICATION->SetAdditionalCSS('https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css'); ?>
<?
function printTree($array)
{
    foreach ($array as $item) { ?>
    <?if(!empty($item['INFO'])){?>
        <ul class="Container">
            <li class="Node ExpandClosed">
                <div class="Expand"></div>
                <div class="Content"><?= $item['INFO']['TITLE'] ?></div><br>
                <ul class="Container">
                    <li class="Node ExpandClosed">
                        <div class="grid">
                            <div class="grid__item">
                                <p class="grid__item-title">Описание</p>
                                <? if (!empty($item['INFO']['DESCRIPTION'])) { ?>
                                    <p class="grid__item-info"><?= $item['INFO']['DESCRIPTION'] ?></p>
                                <? } else { ?>
                                    <p class="grid__item-info">У данного курса отсутствует описание</p>
                                <? } ?>
                            </div>
                            <div class="grid__item">
                                <p class="grid__item-title">Дата назначения курса</p>
                                <? if (!empty($item['COURSE_START_DATE'])) { ?>
                                    <p class="grid__item-info"><?= $item['COURSE_START_DATE']; ?></p>
                                <? } else { ?>
                                    <p class="grid__item-info">У данного курса отсутствует старта</p>
                                <? } ?>
                            </div>
                            <div class="grid__item">
                                <p class="grid__item-title">Срок выполнения курса</p>
                                <? if (!empty($item['COURSE_DUE_DATE'])) { ?>
                                    <p class="grid__item-info"><?= $item['COURSE_DUE_DATE']; ?></p>
                                <? } else { ?>
                                    <p class="grid__item-info">У данного курса нет срока выполнения</p>
                                <? } ?>
                            </div>
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
                                <p class="grid__item-title">Прогресс прохождения</p>
                                <? if ($item['STATUS']['USERID'] && ($item['STATUS']['USERID'] == $item['CURRENT_USER_ID'])) { ?>
                                    <p class="grid__item-info"><?= $item['STATUS']['PROGRESS']; ?></p>
                                <? } else { ?>
                                    <? foreach ($item['STATUS'] as $st) { ?>
                                        <? if ($st['USERID'] === $item['CURRENT_USER_ID']) { ?>
                                            <p class="grid__item-info"><?= $st['PROGRESS']; ?></p>
                                        <? }; ?>
                                    <? }; ?>
                                <? }; ?>
                            </div>
                            <div class="grid__item">
                                <p class="grid__item-title">Дата завершения траектории обучения, курса или материала</p>
                                <? if ($item['STATUS']['USERID'] && ($item['STATUS']['USERID'] == $item['CURRENT_USER_ID'])) { ?>
                                    <p class="grid__item-info"><?= $item['STATUS']['COMPLETIONDATE']; ?></p>
                                <? } else { ?>
                                <? foreach ($item['STATUS'] as $st) { ?>
                                    <? if ($st['USERID'] === $item['CURRENT_USER_ID']) { ?>
                                        <? if (!empty($st['COMPLETIONDATE']) && isset($st['COMPLETIONDATE'])) { ?>
                                            <p class="grid__item-info"><?= $st['COMPLETIONDATE']; ?></p>
                                        <? } else { ?>
                                            <p class="grid__item-info">Курс ещё не завершен</p>
                                        <? }; ?>
                                    <?  }; ?>
                                <? }; ?>
                                <?};?>
                            </div>
                            <div class="grid__item">
                                <p class="grid__item-title">Ссылка на сертификат об окончании курса</p>
                                <? if (!empty($item['INFO']['ISSUEDCERTIFICATEID'])) { ?>
                                    <a href="<?= $item['INFO']['ISSUEDCERTIFICATEID'] ?>" class="grid__item-info"><?= $item['INFO']['ISSUEDCERTIFICATEID'] ?></a>
                                <? } else { ?>
                                    <p class="grid__item-info">У вас пока нет сертификата</p>
                                <? } ?>
                            </div>
                            <div class="grid__item">
                                <p class="grid__item-title">Дата просмотра траектории обучения, курса или материала</p>
                                <? if ($item['STATUS']['USERID'] && ($item['STATUS']['USERID'] == $item['CURRENT_USER_ID'])) { ?>
                                    <p class="grid__item-info"><?= $item['STATUS']['LASTVIEWDATE']; ?></p>
                                <? } else { ?>
                                <? foreach ($item['STATUS'] as $st) { ?>
                                    <? if ($st['USERID'] === $item['CURRENT_USER_ID']) { ?>
                                        <p class="grid__item-info"><?= $st['LASTVIEWDATE']; ?></p>
                                    <? }; ?>
                                <? }; ?>
                                <?};?>
                            </div>
                        
                            <div class="grid__item">
                                <p class="grid__item-title">Ссылка на просмотр курса</p>
                                <a target="_blank" class="wr-button wr-butt-detailed grid__item-info" href="<?= $item['INFO']['VIEWURL'] ?>">Подробнее</a>
                            </div>
                        </div>
                    </li>
                </ul>
                <br>
            </li>
        </ul>
    <? }; ?>
    <? }; ?>
<? }; ?>

<?if(isset($arResult['AUTH_LINK'])){?>
    <a target="_blank" href="<?=$arResult['AUTH_LINK']?>">Ссылка на сайт Ispring</a></br></br>
<?};?>

<?if(!empty($arResult['COURSE_INFO'])){?>
    <div id="tree" class="courses-tree" onclick="tree_toggle(arguments[0])">
        <? printTree($arResult['COURSE_INFO']); ?>
    </div>
<?}else{?>
    <span>У вас ещё нет назначенных курсов</span>
<? };?>
