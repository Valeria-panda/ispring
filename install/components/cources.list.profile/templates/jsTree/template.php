<? $APPLICATION->SetAdditionalCSS('https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css'); ?>
<? $APPLICATION->AddHeadScript("https://code.jquery.com/ui/1.11.4/jquery-ui.js"); ?>
<? $APPLICATION->SetAdditionalCSS('https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css'); ?>
<span><?= $arResult['SUCCESS_AUTH_URL'] ?></span>
<!-- <iframe src="/local/components/openregioncourses/cources.list/templates/.default/frame.php"></iframe> -->
<?// var_dump($arResult['COURSES']);

?>
<div id="tree" class="courses-"  onclick="tree_toggle(arguments[0])">
</div>

<script>
    data = <?= json_encode($arResult['COURSES']) ?>;
    
    function buildTree(tree, prefix) {
        if (typeof prefix === 'undefined')
            prefix = '<ul class="Container"><li class="Node IsRoot ExpandClosed">';
            var result = '';
            tree.forEach(function(e, i) {
                var lastNode = i == tree.length - 1;
                result += prefix +'<div class="Expand"></div>'+'<div class="Content">' + e.TITLE + '</div><br>'; 
                if (e.CHILDS)
                    result += ('<ul class="Container">' + buildTree(e.CHILDS,'<li class="Node ExpandClosed">' ));
            });
            return result + '</ul>';
    }
    
    document.getElementById("tree").innerHTML = buildTree(data).split('\n').join('<br/>');

</script>

<table id="courses-list">
    <thead>
        <tr>
            <td class="center">Описание</td>
            <td class="center">Статус</td>
            <td></td>
        </tr>
    </thead>
    <tbody>
        <? foreach ($arResult['COURSES'] as $course) : ?>
            <tr>
                <td>
                    <b><?= $course['TITLE'] ?></b><br>
                </td>
                <td class="center"><?= ($course['STATUS_TEXT'] == 1 ? 'Опубликован' : 'Не опубликован') ?></td>
                <td>
                    <a class="wr-button wr-butt-detailed" href="<?= $course['DETAIL_PAGE_URL'] ?>">Подробнее</a>
                </td>
            </tr>
        <? endforeach; ?>
    </tbody>
</table>
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
