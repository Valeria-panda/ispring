<? $APPLICATION->SetAdditionalCSS('https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css'); ?>
<? $APPLICATION->AddHeadScript("https://code.jquery.com/ui/1.11.4/jquery-ui.js"); ?>
<? $APPLICATION->SetAdditionalCSS('https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css'); ?>
<?
function printTree ($array, $deep = 1){
    
    if ($deep > 20) { exit('Error deep > 20'); } 
    
    foreach ($array as $item) {
        
        if ($item['CHILDS']) {
            if ($deep == 1) {?>
                <ul class="Container">
                    <li class="Node IsRoot ExpandClosed">    
            <?}
            else {?>
                <ul class="Container"> 
                    <li class="Node ExpandClosed"> 
            <?}?>
            <div class="Expand"></div>
            <div class="Content"> <?=$item['TITLE']?></div><br>
            <?
            $deep += 1;
            printTree($item['CHILDS'], $deep);
        }
        else
        {
            if ($item['TYPE'] == 'Материал iSpring Suite') {?>
                <ul class="Container"> 
                    <li class="Node ExpandClosed">
                        <div class="Expand"></div>
                        <div class="Content"><?=$item['TITLE']?></div><br>  
                        <ul class="Container"><li class="Node ExpandClosed">
                            <table id="courses-list">    
                            <thead>
                                <tr>
                                    <td class="center">Описание</td>
                                    <td class="center">Статус</td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <b><?= $item['DESCRIPTION'] ?></b><br>
                                    </td>
                                    <td class="center"><?= ($item['STATUS_TEXT'] == 1 ? 'Опубликован' : 'Не опубликован') ?></td>
                                    <td>
                                        <a class="wr-button wr-butt-detailed" href="<?= $item['VIEWURL']/*$item['DETAIL_PAGE_URL']*/ ?>">Подробнее</a>
                                    </td>
                                </tr>
                            </tbody>
                            </table>
                        </li></ul>
                    </li>
                </ul> 
            <?}
        }
    }
    ?>
            </li>
        </ul>
    <?
}

?>
<div id="tree" class="courses-tree"  onclick="tree_toggle(arguments[0])">
    <? printTree($arResult['COURSES']);?>
</div>

<? if ($arResult['PAGES'] > 1) : ?>
    <div class="NavPage">
        <p>Страницы</p>
        <ul>
            <?
            for ($i = $arResult['PAGE_START']; $i <= $arResult['PAGE_END']; $i++) : ?>
                <? if ($i == $arParams['PAGE']) : ?>
                    <li class="active"><a><?= $i ?></a></li>
                <? else : ?>
                    <? $url = $APPLICATION->GetCurPageParam("page=" . $i, array("page")); ?>
                    <li class=""><a href="<?= $url ?>"><?= $i ?></a></li>
                <? endif; ?>
            <? endfor; ?>
        </ul>
    </div>
<? endif; ?>
