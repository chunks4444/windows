<?php
// 문 설정 탭의 '살 바꾸기' — 문틀·문짝 설정은 그대로 두고 다른 살 모양(엔진)으로 옮겨 간다.
// 상단 메뉴가 만든 $navStudioItems/$navStudioActive를 그대로 쓴다.
// 고르면 engine-rail.js가 확인창 → 문 설정 보관 → ?keep=door 로 이동한다.
if (empty($navStudioItems)) return;
?>
<div class="ctrl engine-switch">
    <div class="ctrl-header"><span class="ctrl-label"><?= te('살 바꾸기') ?></span></div>
    <select id="selEngineSwitch" class="sb-select">
        <?php foreach ($navStudioItems as $navItem):
            $navKey = $navItem['engine_key'];
            $isCur  = (bool)($navStudioActive[$navKey] ?? false);
        ?>
        <option value="<?= htmlspecialchars(lang_href('/src/engine/' . $navKey . '/' . $navKey . '.php')) ?>"<?= $isCur ? ' selected' : '' ?>><?= htmlspecialchars(term($navItem['title'])) ?></option>
        <?php endforeach; ?>
    </select>
</div>
