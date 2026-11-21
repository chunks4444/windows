<?php
// 툴 레일 '내 도면'의 엔진 필터 — 전체 도면을 보여 주고 엔진별로 좁힌다. 목록·열기는 engine-rail.js(initMinePane)가 맡는다.
// 엔진 이름·주소는 상단 메뉴가 만든 $navStudioItems를 그대로 쓴다. option의 data-href는 다른 엔진 도면을 열 때 보내는 주소.
if (empty($navStudioItems)) return;
?>
<select id="mineEngineFilter" class="sb-select">
    <option value=""><?= te('전체') ?></option>
    <?php foreach ($navStudioItems as $navItem):
        $navKey = $navItem['engine_key'];
    ?>
    <option value="<?= htmlspecialchars($navKey) ?>" data-href="<?= htmlspecialchars(lang_href('/src/engine/' . $navKey . '/' . $navKey . '.php')) ?>"><?= htmlspecialchars(term($navItem['title'])) ?></option>
    <?php endforeach; ?>
</select>
